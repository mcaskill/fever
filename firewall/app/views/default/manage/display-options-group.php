<table>
	<tr>
		<th class="proto">
			Item sort order
		</th>
		<td>
<?php foreach($this->sort_order as $value => $text):?>
			<label>
				<input type="radio" name="group[sort_order]" onchange="one('#group_sort_order').checked=false;" value="<?php e($value);?>"<?php e(($this->option('sort_order', 0, $group['id']) == $value) ? ' checked="checked"' : '')?> />
				<?php e($text);?> &nbsp;

			</label>
<?php endforeach;?>
		</td>
		<td>
			<label>
				<span class="i"><input type="checkbox" id="group_sort_order" name="group[sort_order]" value="-1"<?php e(($group['sort_order'] == -1) ? ' checked="checked"' : '')?> /></span>
				global default
			</label>
		</td>
	</tr>
	<tr>
		<th>
			<label for="group_item_allows">Item content</label>
		</th>
		<td>
				<span class="w"><select name="group[item_allows]" onchange="one('#group_item_allows').checked=false;">
<?php foreach($this->item_allows as $value => $text):?>
					<option value="<?php e($value);?>"<?php e(($this->option('item_allows', 0, $group['id']) == $value) ? ' selected="selected"' : '');?>><?php e($text);?></option>
<?php endforeach;?>
				</select></span>
			</label>
		</td>
			<td>
				<label>
					<span class="i"><input type="checkbox" id="group_item_allows" name="group[item_allows]" value="-1"<?php e(($group['item_allows'] == -1) ? ' checked="checked"' : '')?> /></span>
					global default
				</label>
			</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="hidden" name="group[item_excerpts]" value="0" />
			<label>
				<span class="i"><input type="checkbox" name="group[item_excerpts]" onchange="one('#group_item_excerpts').checked=false;" value="1"<?php e(($this->option('item_excerpts', 0, $group['id']) > 0) ? ' checked="checked"' : '')?> /></span>
				excerpt feed items <span class="btn help" onmouseover="Fever.displayHelp(this, 'excerpts');">?</span>
			</label>
		</td>
		<td>
			<label>
				<span class="i"><input type="checkbox" id="group_item_excerpts" name="group[item_excerpts]" value="-1"<?php e(($group['item_excerpts'] == -1) ? ' checked="checked"' : '')?> /></span>
				global default
			</label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="hidden" name="group[unread_counts]" value="0" />
			<label>
				<span class="i"><input type="checkbox" name="group[unread_counts]" onchange="one('#group_unread_counts').checked=false;" value="1"<?php e(($this->option('unread_counts', 0, $group['id']) > 0) ? ' checked="checked"' : '')?> /></span>
				show unread counts
			</label>
		</td>
		<td>
			<label>
				<span class="i"><input type="checkbox" id="group_unread_counts" name="group[unread_counts]" value="-1"<?php e(($group['unread_counts'] == -1) ? ' checked="checked"' : '')?> /></span>
				global default
			</label>
		</td>
	</tr>
</table>