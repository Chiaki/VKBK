<?php
/*
	vkbk :: sync.php
	since v0.8.9
*/

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('./cfg.php');

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get Functions
require_once(ROOT.'classes/func.php');
$f = new func();

// Get local counters for top menu
$lc = $db->query_row("SELECT * FROM vk_counters");

print $skin->header(array('extend'=>''));
print $skin->navigation($lc);

$sync_apvd = '/ajax/sync.php?';
$sync_wall = '/ajax/sync-wall.php?';
$sync_comm = '/ajax/sync-comm.php?';

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="fa fa-sync"></i> Синхронизация</span>
    </nav>
</div>
<div class="container">
	<div class="row">	
		<div class="col-sm-8 my-3 p-3 bg-white rounded box-shadow border-right border-grey">
			<div class="table-responsive" style="overflow-x:hidden;max-height:450px;">
				<div id="d-log"></div>
			</div>
		</div>
		<div class="col-sm-4 my-3 p-3 bg-white rounded box-shadow">
			<div class="row mb-4 pb-4 border-bottom border-grey">
				<div class="col-sm-12">
					<div id="sync-progress" class="text-center"><h6 class="pb-2 mb-0" style="display:inline-block;">Прогресс</h6></div>
					<div class="progress">
						<div class="progress-bar progress-bar-striped bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="s-total"></div>
					</div>
				</div>
			</div>
			<div id="sync-opts">
				<div class="row">
					<div class="col-sm-12 text-center">
						<div class="text-center"><h6 class="pb-2 mb-0">Синхронизировать</h6></div>
<button data-uri="{$sync_apvd}do=albums" type="button" class="btn btn-sm btn-block btn-outline-success">
<i class="far fa-fw fa-folder-open"></i> Альбомы</button>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-sm-6 pr-1">
<button data-uri="{$sync_apvd}do=photo&fast=1" type="button" class="btn btn-sm btn-block btn-outline-success">
<i class="fa fa-fw fa-image"></i> Фото (<i class="fa fa-fw fa-hourglass-end"></i> быстр.)</button>
					</div>
					<div class="col-sm-6 pl-1">
<button data-uri="{$sync_apvd}do=photo" type="button" class="btn btn-sm btn-block btn-outline-success">
<i class="fa fa-fw fa-image"></i> Фото (<i class="fa fa-fw fa-hourglass"></i> все)</button>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-sm-12">
<button data-uri="{$sync_apvd}do=video" type="button" class="btn btn-sm btn-block btn-outline-success">
<i class="fa fa-fw fa-film"></i> Видеозаписи</button>
<button data-uri="{$sync_apvd}do=docs" type="button" class="btn btn-sm btn-block btn-outline-success">
<i class="far fa-fw fa-file"></i> Документы</button>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-sm-6 pr-1">
<button data-uri="{$sync_wall}do=wall&offset=0&fast=1" type="button" class="btn btn-sm btn-block btn-outline-success"><i class="far fa-fw fa-newspaper"></i> Стена (<i class="fa fa-fw fa-hourglass-end"></i> быстр.)</button>
					</div>
					<div class="col-sm-6 pl-1">
<button data-uri="{$sync_wall}do=wall&offset=0" type="button" class="btn btn-sm btn-block btn-outline-success"><i class="far fa-fw fa-newspaper"></i> Стена (<i class="fa fa-fw fa-hourglass"></i> всё)</button>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-sm-12">
<a href="/sync-messages.php" class="btn btn-sm btn-block btn-outline-success">
<i class="far fa-fw fa-comments"></i> Сообщения (диалоги)</a>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-sm-12">
<button data-uri="{$sync_comm}do=check&offset=0" type="button" class="btn btn-sm btn-block btn-outline-success"><i class="far fa-fw fa-comment-alt"></i> Комментарии</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<input type="hidden" id="s-min" size="5" value="0" />
		<input type="hidden" id="s-max" size="5" value="0" />
	</div>
</div>
E;

$ex_bot = <<<E
<script type="text/javascript">
// Default options
var syncDefTime = 10;

var syncProcess = '<div id="busy" style="position:absolute;top:0;right:1em;">';
    syncProcess += '<i class="fas fa-spinner fa-pulse text-secondary"></i>';
    syncProcess += ' <span>'+syncDefTime+'</span></div>';

var smin = 0;
var smax = 0;
var minval = jQuery("#s-min");
var total  = jQuery("#s-total");

var spin_counter = null;
var spin_count = syncDefTime;

$(document).ready(function() {
	// Bind opts
	jQuery("#sync-opts button").on('click', function(){
		// Separator
		jQuery("#d-log").prepend('<div class="p-1 m-1 border-bottom border-grey"></div>');
		// Clean progress bar if we synced before
		progress_change(minval,0,total,0);
		// Buttons off!
		jQuery("#sync-opts button").attr("disabled", true);
		jQuery("#sync-progress").append(syncProcess);
		sync_query(jQuery(this).data("uri"));
	});
});

/*
	Function sync_query
	In:
	uri - query url		(string)
*/
function sync_query(uri){
	jQuery.ajax({
		async : false, method : "GET", url : uri
	}).done( function(data){
		var r = jQuery.parseJSON(data);
		// Show log
		jQuery.each( r.response.msg, function( i, item ) {
			jQuery(item).prependTo("#d-log");
		});
		// Update progress countter
		if(r.error == false){
			if(r.response.done > 0){
				var mnext = (r.response.next_uri != '') ? 1 : 0;
				update_count(r.response.done,r.response.total,mnext);
			}
			// Check next URL and do a query if yes...
			if(r.response.next_uri != ''){
				// Update countdown between requests
				if(r.response.timer > 0){
					spin_count = r.response.timer;
					syncDefTime = r.response.timer;
				}
				spin_counter = setInterval(function(){
					spin_count = spin_count-1;
					if(spin_count <= 0) {
						// We done, set spin_count back to defined and process
						clearInterval(spin_counter);
						spin_count = syncDefTime;
						sync_query(r.response.next_uri);
					} else {
						// Just a second...
						jQuery("#busy span").html(spin_count);
					}
				}, 1000);
			}
			if(r.response.next_uri == ''){
				jQuery("#busy").remove();
				jQuery("#sync-opts button").attr("disabled", false);
			}
		}
		
		if(r.error == true){
			jQuery(r.response.error_msg).prependTo("#d-log");
		}
	});
}

/*
	Function: update_count
	In:
	c  - count			(int)
	tc - total count	(int)
	n  - next or finish	(bool)
*/
function update_count(c,tc,n){
	var a = Math.floor(minval.val());
	var a1 = a+c; // number of processed items
	
	// Check `dmax` if not set, update from response
	if(smax == 0){ smax = tc; total.val(tc); }
	if(a1 != smax && a1 < smax){
		var p = Math.floor(a1 / (smax / 100));
		progress_change(minval,a1,total,p);
	}
	if(a1 >= smax){
		progress_change(minval,smax,total,100);		
		// Dialogs sync finished, stopping task
		minval.val(0); minval.change();
		total.val(0); total.change();
		smin = 0; smax = 0;
	}
}

/*
	Function progress_change
	In:
	selector - min value		(object)
	value    - current value	(object)
	total    - total selector	(selector)
	percent  - current percent	(int)
*/
function progress_change(selector,value,total,percent){
	selector.val(value);
	selector.change();
	total.css("width",percent+"%");
}

</script>
E;

if(!$cfg['pj']){
	print $skin->footer(array('extend'=> $ex_bot));
} else {
	print $ex_bot;
}

$db->close($res);

?>