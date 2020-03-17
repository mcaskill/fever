<?php $this->render('page/header');?>
<h1>Keyboard Shortcuts</h1>

<div id="shortcuts">
<table>
	<tr>
		<th>Shortcut</th>
		<th>Command</th>
	</tr>

	<tr>
		<td>1</td>
		<td>Go to Hot</td>
	</tr>
	<tr>
		<td>2</td>
		<td>Go to Kindling</td>
	</tr>
	<tr>
		<td>3</td>
		<td>Go to Saved</td>
	</tr>
	<tr>
		<td>4</td>
		<td>Go to Sparks</td>
	</tr>
	<tr>
		<td>5</td>
		<td>Go to Search results</td>
	</tr>

	<tr>
		<td>Z</td>
		<td>Unread the most recently read items</td>
	</tr>
	<tr>
		<td>A</td>
		<td>Mark current group or feed as read</td>
	</tr>
	<tr>
		<td><span class="shift">A</span></td>
		<td>Mark all as read</td>
	</tr>
	<tr>
		<td>R</td>
		<td>Refresh all feeds</td>
	</tr>
	<tr>
		<td><span class="shift">R</span></td>
		<td>Refresh current group or feed</td>
	</tr>
	<tr>
		<td>S</td>
		<td>Save or unsave the current item</td>
	</tr>
	<tr>
		<td>N</td>
		<td>Add a new feed</td>
	</tr>
	<tr>
		<td><span class="shift">N</span></td>
		<td>Create a new group</td>
	</tr>
	<tr>
		<td>U</td>
		<td>Show or hide read items</td>
	</tr>
	<tr>
		<td>F</td>
		<td>Show or hide list of feeds</td>
	</tr>
	<tr>
		<td>P</td>
		<td>Display Fever preferences</td>
	</tr>
	<tr>
		<td>Spacebar</td>
		<td>Scroll to next item or link or page down</td>
	</tr>
	<tr>
		<td><span class="shift">Spacebar</span></td>
		<td>Scroll to previous item or link or page up</td>
	</tr>
	<tr>
		<td>K</td>
		<td>Scroll to previous item or link</td>
	</tr>
	<tr>
		<td>J</td>
		<td>Scroll to next item or link</td>
	</tr>
	<tr>
		<td>0 <em>or</em> ↵</td>
		<td>Toggle current item excerpt or full content</td>
	</tr>
	<tr>
		<td>B</td>
		<td>Add current link to blacklist</td>
	</tr>
	<tr>
		<td><span class="shift">B</span></td>
		<td>Display blacklist</td>
	</tr>
	<tr>
		<td>O <em>or</em> <span class="symbols">&rarr;</span></td>
		<td>Open current item or link</td>
	</tr>
	<tr>
		<td>V</td>
		<td>Visit site of active item</td>
	</tr>
	<tr>
		<td><span class="shift">V</span></td>
		<td>Visit site and mark its items as read</td>
	</tr>
	<tr>
		<td>I</td>
		<td>Edit the current group or feed</td>
	</tr>
	<tr>
		<td>esc</td>
		<td>Clear search or dismiss dialog</td>
	</tr>
	<tr>
		<td>/</td>
		<td>Focus search input</td>
	</tr>
	<tr>
		<td class="symbols">&larr; &rarr;</td>
		<td>Navigate from group list to feed list to items</td>
	</tr>
	<tr>
		<td class="symbols">&uarr; &darr;</td>
		<td>Navigate to previous or next group or feed</td>
	</tr>
	<tr>
		<td class="symbols"><span class="shift">&uarr;</span> <span class="shift">&darr;</span></td>
		<td>Previous or next contributing item to current link</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th>Shortcut</th>
		<th>Send to</th>
	</tr>
	<?php foreach($this->prefs['services'] as $i=>$service): ?>
	<tr>
		<td><?php e(up($service['key']));?></td>
		<td><?php e($service['name']);?></td>
	</tr>
	<?php endforeach; ?>
</table>
</div>

<p class="btn-row"><a class="btn text" href="./">Done<i></i></a></p>

<?php $this->render('page/footer');?>