		<table>
			<tr>
				<th class="proto">
					Item sort order
				</th>
				<td>
<?php foreach($this->sort_order as $value => $text):?>
					<label>
						<input type="radio" name="feed[sort_order]" onchange="one('#feed_sort_order').checked=false;" value="<?php e($value);?>"<?php e(($this->option('sort_order', $feed['id']) == $value) ? ' checked="checked"' : '')?> />
						<?php e($text);?> &nbsp;
					</label>
<?php endforeach;?>
				</td>
				<td>
					<label>
						<span class="i"><input type="checkbox" id="feed_sort_order" name="feed[sort_order]" value="-1"<?php e(($feed['sort_order'] == -1) ? ' checked="checked"' : '')?> /></span>
						group default
					</label>
				</td>
			</tr>
			<tr>
				<th>
					<label for="feed_item_allows">Item content</label>
				</th>
				<td>
					<span class="w"><select name="feed[item_allows]" onchange="one('#feed_item_allows').checked=false;">
<?php foreach($this->item_allows as $value => $text):?>
							<option value="<?php e($value);?>"<?php e(($this->option('item_allows', $feed['id']) == $value) ? ' selected="selected"' : '');?>><?php e($text);?></option>
<?php endforeach;?>
					</select></span>
				</td>
					<td>
						<label>
							<span class="i"><input type="checkbox" id="feed_item_allows" name="feed[item_allows]" value="-1"<?php e(($feed['item_allows'] == -1) ? ' checked="checked"' : '')?> /></span>
							group default
						</label>
					</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="hidden" name="feed[item_excerpts]" value="0" />
					<label>
						<span class="i"><input type="checkbox" name="feed[item_excerpts]" onchange="one('#feed_item_excerpts').checked=false;" value="1"<?php e(($this->option('item_excerpts', $feed['id']) > 0) ? ' checked="checked"' : '')?> /></span>
						excerpt feed items <span class="btn help" onmouseover="Fever.displayHelp(this, 'excerpts');">?</span>
					</label>
				</td>
				<td>
					<label>
						<span class="i"><input type="checkbox" id="feed_item_excerpts" name="feed[item_excerpts]" value="-1"<?php e(($feed['item_excerpts'] == -1) ? ' checked="checked"' : '')?> /></span>
						group default
					</label>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="hidden" name="feed[unread_counts]" value="0" />
					<label>
						<span class="i"><input type="checkbox" name="feed[unread_counts]" onchange="one('#feed_unread_counts').checked=false;" value="1"<?php e(($this->option('unread_counts', $feed['id']) > 0) ? ' checked="checked"' : '')?> /></span>
						show unread counts
					</label>
				</td>
				<td>
					<label>
						<span class="i"><input type="checkbox" id="feed_unread_counts" name="feed[unread_counts]" value="-1"<?php e(($feed['unread_counts'] == -1) ? ' checked="checked"' : '')?> /></span>
						group default
					</label>
				</td>
			</tr>
		</table>