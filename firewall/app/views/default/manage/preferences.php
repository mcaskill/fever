<form method="post" action="<?php e(errors_url('./?manage'));?>" class="tabbed">
	<input type="hidden" name="action" value="save-preferences" />

	<h1>Preferences</h1>

	<ul class="tabs">
		<li class="active"><a href="#prefs-behavior">Behavior</a></li>
		<li><a href="#prefs-display">Display</a></li>
		<li><a href="#prefs-refreshing">Refreshing</a></li>
		<li><a href="#prefs-sharing">Sharing</a></li>
		<li><a href="#prefs-login">Login</a></li>
	</ul>

	<div id="prefs-behavior" class="tab active">
		<h2>Behavior</h2>
		<fieldset>
			<table>
				<tr>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="new_window" value="1"<?php e(($this->prefs['new_window']) ? ' checked="checked"' : '')?> /></span>
							open links in new window/tab
						</label>
					</td>
				</tr>
				<tr>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="anonymize" value="1"<?php e(($this->prefs['anonymize']) ? ' checked="checked"' : '')?> /></span>
							anonymize referrers from this Fever installation
						</label>
					</td>
				</tr>
				<tr>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="auto_read" value="1"<?php e(($this->prefs['auto_read']) ? ' checked="checked"' : '')?> /></span>
							mark items as read as they scroll past
						</label>
					</td>
				</tr>
				<tr>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="auto_spark" value="1"<?php e(($this->prefs['auto_spark']) ? ' checked="checked"' : '')?> /></span>
							add new feeds to Sparks
						</label>
					</td>
				</tr>
				<tr>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="auto_update" value="1"<?php e(($this->prefs['auto_update']) ? ' checked="checked"' : '')?> /></span>
							automatically install updates
						</label>
					</td>
				</tr>
				<tr>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="toggle_click" value="1"<?php e(($this->prefs['toggle_click']) ? ' checked="checked"' : '')?> /></span>
							toggle item excerpts on click
						</label>
					</td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="prefs-display" class="tab">
		<h2>Display</h2>
		<fieldset>
			<table>
				<tr>
					<th>
						Degrees
					</th>
					<td>
						<label>
							<input type="radio" name="use_celsius" value="0"<?php e(($this->prefs['use_celsius']) ? '' : ' checked="checked"')?> />
							fahrenheit &nbsp;
						</label>
						<label>
							<input type="radio" name="use_celsius" value="1"<?php e(($this->prefs['use_celsius']) ? ' checked="checked"' : '')?> />
							celsius
						</label>
					</td>
				</tr>
				<tr>
					<th>
						Layout
					</th>
					<td>
						<label>
							<input type="radio" name="layout" value="0"<?php e(!$this->prefs['layout'] ? ' checked="checked"' : '')?> />
							fixed &nbsp;
						</label>
						<label>
							<input type="radio" name="layout" value="1"<?php e($this->prefs['layout'] ? ' checked="checked"' : '')?> />
							fluid &nbsp;
						</label>
					</td>
				</tr>
				<tr>
					<th class="proto">
						Item sort order
					</th>
					<td class="proto">
	<?php foreach($this->sort_order as $value => $text):?>
						<label>
							<input type="radio" name="sort_order" value="<?php e($value);?>"<?php e(($this->prefs['sort_order'] == $value) ? ' checked="checked"' : '')?> />
							<?php e($text);?> &nbsp;
						</label>
	<?php endforeach;?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="item_allows">Item content</label>
					</th>
					<td>
						<span class="w"><select name="item_allows" id="item_allows">
	<?php foreach($this->item_allows as $value => $text):?>
							<option value="<?php e($value);?>"<?php e(($this->prefs['item_allows'] == $value) ? ' selected="selected"' : '');?>><?php e($text);?></option>
	<?php endforeach;?>
						</select></span>
					</td>
				<tr>
					<td></td>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="item_excerpts" value="1"<?php e(($this->prefs['item_excerpts']) ? ' checked="checked"' : '')?> /></span>
							excerpt items
						</label>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="unread_counts" value="1"<?php e(($this->prefs['unread_counts']) ? ' checked="checked"' : '')?> /></span>
							show unread counts
						</label>
					</td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="prefs-refreshing" class="tab">
		<h2>Refreshing</h2>
		<fieldset>
			<table>
				<tr>
					<td colspan="2">
						Delete unsaved items older than
						<select name="item_expiration">
							<?php foreach($this->exp_range as $i):?>
							<option value="<?php e($i);?>"<?php e(($this->prefs['item_expiration']==$i) ? ' selected="selected"' : '')?>><?php e($i);?> weeks</option>
							<?php endforeach;?>
						</select>
						<span class="btn help" onmouseover="Fever.displayHelp(this, 'item-expiration');">?</span>
					</td>
				</tr>
				<tr>
					<th>Auto-refresh</th>
					<td>
						<label>
							<input type="radio" name="auto_refresh" value="1"<?php e(($this->prefs['auto_refresh']) ? ' checked="checked"' : '')?> />
							in browser (with or without cron) &nbsp;
						</label>
						<label>
							<input type="radio" name="auto_refresh" value="0"<?php e(($this->prefs['auto_refresh']) ? '' : ' checked="checked"')?> />
							via cron only
						</label>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="auto_reload" value="1"<?php e(($this->prefs['auto_reload']) ? ' checked="checked"' : '')?> /></span>
							reload interface after browser refresh
						</label>
					</td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="prefs-sharing" class="tab">
		<h2>Sharing</h2>
		<fieldset>
			<table>
				<tr>
					<td>
						<label>
							<span class="i"><input type="checkbox" name="share" value="1"<?php e(($this->prefs['share']) ? ' checked="checked"' : '')?> /></span>
							publish a feed of the 30 most recent <a href="./?rss=saved" class="btn text feed">Saved Items<i></i></a>
							<span class="btn help" onmouseover="Fever.displayHelp(this, 'saved-feed');">?</span>
						</label>
					</td>
				</tr>
			</table>
			<table class="embedded" id="services">
				<tr>
					<th><strong>Service name</strong></th>
					<th><strong>Service url <span class="btn help" onmouseover="Fever.displayHelp(this, 'service-url');">?</span></strong></th>
					<th class="key"><strong>Key</strong></th>
					<th><strong><span class="btn add" onclick="Fever.Reader.addNewService();">Add<i></i></span></strong></th>
				</tr>
				<?php foreach($this->prefs['services'] as $i=>$service): ?>
					<tr id="service-<?php e($i);?>-row" class="a-service">
						<td><span class="w"><input type="text" name="service[<?php e($i);?>][name]" value="<?php e($service['name']);?>" /></span></td>
						<td><span class="w"><input type="text" name="service[<?php e($i);?>][url]" value="<?php e(h($service['url']));?>" /></span></td>
						<td class="key"><span class="w"><input type="text" maxlength="1" name="service[<?php e($i);?>][key]" value="<?php e($service['key']);?>" /></span></td>
						<td><span class="btn cancel" onclick="Fever.Reader.deleteService('#service-<?php e($i);?>-row');">Delete<i></i></span></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div id="prefs-login" class="tab">
		<h2>Login</h2>
		<fieldset>
			<table>
				<tr>
					<th>Email</th>
					<td><span class="w"><input type="email" name="email" value="<?php echo $this->cfg['email'];?>" autocomplete="off" /></span></td>
				</tr>
				<tr>
					<th class="proto">Password</th>
					<td class="proto"><span class="w"><input type="password" name="password" value="<?php echo $this->cfg['password'];?>" autocomplete="off" /></span></td>
				</tr>
			</table>
		</fieldset>
	</div><!-- /.tab -->

	<div class="btn-row">
		<fieldset>
			<span class="btn text" onclick="Fever.dismissDialog();">Cancel<i></i></span>
			<button><span class="btn text default">Save<i></i></span></button>
		</fieldset>
	</div>
</form>

<table id="service-template">
	<tr id="service-row" class="a-service">
		<td><span class="w"><input type="text" name="service[name]" /></span></td>
		<td><span class="w"><input type="text" name="service[url]" /></span></td>
		<td class="key"><span class="w"><input type="text" maxlength="1" name="service[key]" /></span></td>
		<td><span class="btn cancel" onclick="Fever.Reader.deleteService('#service-row');">Delete<i></i></span></td>
	</tr>
</table>