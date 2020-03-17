	</div><!-- #screens -->
</div><!-- #screens-container -->

<?php if($this->is_logged_in()):?>

<div id="dialog-container">
	<div class="box">
		<div id="dialog">


		</div>
	</div>
</div>

<div id="dialog-preferences" class="dialog-template">
	<a onclick="Fever.iPhone.dismissPreferences();" class="close">Close</a>
	<ul>
		<li>
			<label for="action-show-read" class="checkbox" onclick="Fever.iPhone.toggleUnread(this);">
				<input type="checkbox" id="action-show-read" value="1" />
				Show read
			</label>
		</li>
		<li>
			<label for="action-show-feeds" class="checkbox" onclick="Fever.iPhone.toggleFeeds(this);">
				<input type="checkbox" id="action-show-feeds" value="1" />
				Show feeds
			</label>
		</li>
		<li>
			<label for="action-elsewhere" class="checkbox" onclick="Fever.iPhone.toggleElsewhere(this);">
				<input type="checkbox" id="action-elsewhere" value="1" />
				View external links in app
			</label>
		</li>
	</ul>

	<h2>Mark as read on</h2>

	<ul id="action-mark-as-read">
		<li>
			<label for="action-read-scroll" class="checkbox" onclick="Fever.iPhone.toggleReadOnScroll(this);">
				<input type="checkbox" id="action-read-scroll" value="1" />
				scroll past headline
			</label>
		</li>
		<li>
			<label for="action-read-back" class="checkbox" onclick="Fever.iPhone.toggleReadOnBackOut(this);">
				<input type="checkbox" id="action-read-back" value="1" />
				back out of group/feed
			</label>
		</li>
	</ul>

	<a onclick="Fever.iPhone.unreadRecentlyRead();" class="btn">Unread most recently read</a>
</div>

<?php
/* // no use right now
<div id="dialog-share-to" class="dialog-template">
	<a onclick="Fever.dismissDialog();" class="close">Close</a>
	<h1>Share to</h1>
	<ul id="share-to">
	<?php foreach($this->prefs['services'] as $i => $service):?>
		<li><a onclick="Fever.iPhone.shareTo(<?php e($i);?>);" class="btn"><?php e($service['name'])?></a></li>
	<?php endforeach;?>
	</ul>
</div>
*/
?>

<?php $this->render('reader/webview'); ?>
<?php endif;?>

</body>
</html>