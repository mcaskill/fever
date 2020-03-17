
		<ul class="box-list content">
			<li<?php e(($this->prefs['ui']['section'] == 0) ? ' class="has-focus"' : ''); ?> id="section-0">
				<a href="./?ui[section]=0" onclick="return Fever.Reader.loadSection(0);"><i class="icon hot"></i> Hot</a>
				<span class="btn help" onmouseover="Fever.displayHelp(this, 'hot');"></span>
				<span id="hot-options">
					<span class="btn menu text" onclick="Fever.displayMenu(this, 'hotRange');"><?php e($this->hot_range[$this->prefs['ui']['hot_range']])?><i></i></span>
					starting
					<span class="btn menu text" onclick="Fever.displayMenu(this, 'hotStart');"><?php e($this->hot_start[$this->prefs['ui']['hot_start']])?><i></i></span>
				</span>
			</li>
			<!-- Kindling -->
			<li class="<?php e(($this->prefs['ui']['section'] == 1 && $this->prefs['ui']['group_id'] == 0) ? 'has-focus' : ''); ?>" id="group-0">
				<a href="./?ui[section]=1&amp;ui[group_id]=0" onclick="return Fever.Reader.loadGroup(0);" class="group-0 <?php e(($this->groups[0]['unread_count']) ? 'unread' : 'read');?><?php e(($this->option('unread_counts', 0, 0) == 0) ? ' hide-unread' : '');?>">
					<i class="icon kindling"></i> Kindling <em class="unread-count"><?php e($this->groups[0]['unread_count'])?></em>
				</a>
				<span class="btn menu group" onclick="Fever.displayMenu(this, 'group', 0);"></span>
				<span class="btn help" onmouseover="Fever.displayHelp(this, 'kindling');"></span>
			</li>
			<!-- Groups -->
			<li id="groups-scroller-container">
				<ul class="box-list" id="groups-scroller">
	<?php foreach($this->groups as $group):?>
	<?php if($group['id'] == 0) continue; ?>
	<?php

	if (!$this->prefs['ui']['show_read'] && !$group['unread_count'])
	{
		continue;
	}

	?>
			<li class="<?php e(($group['id'] != 0) ? 'droppable' : '')?><?php e(($this->prefs['ui']['section'] == 1 && $this->prefs['ui']['group_id'] == $group['id']) ? ' has-focus' : ''); ?>" id="group-<?php e($group['id'])?>">
				<a href="./?ui[section]=1&amp;ui[group_id]=<?php e($group['id'])?>" onclick="return Fever.Reader.loadGroup(<?php e($group['id'])?>);" class="group-<?php e($group['id'])?> <?php e(($group['unread_count']) ? 'unread' : 'read')?><?php e(($this->option('unread_counts', 0, $group['id']) == 0) ? ' hide-unread' : '')?>">
					<?php e(lt($group['title']))?> <em class="unread-count"><?php e($group['unread_count'])?></em>
				</a>
				<span class="btn menu group" onclick="Fever.displayMenu(this, 'group', <?php e($group['id'])?>);"></span>
				<span class="btn refresh" onclick="Fever.Reader.refreshGroup(<?php e($group['id'])?>);"></span>
			</li>
	<?php endforeach;?>
				</ul>
			</li>
			<li<?php e(($this->prefs['ui']['section'] == 2) ? ' class="has-focus"' : ''); ?> id="section-2">
				<a href="./?ui[section]=2" onclick="return Fever.Reader.loadSection(2);"><i class="icon saved"></i> Saved <em class="saved-count"><?php e($this->total_saved)?></em></a>
				<span class="btn help" onmouseover="Fever.displayHelp(this, 'saved');"></span>
			</li>
			<li class="droppable<?php e(($this->prefs['ui']['section'] == 3) ? ' has-focus' : ''); ?>" id="section-3">
				<a href="./?ui[section]=3" onclick="return Fever.Reader.loadSection(3);"><i class="icon spark"></i> Sparks</a>
				<span class="btn menu group" onclick="Fever.displayMenu(this, 'sparks');"></span>
				<span class="btn help" onmouseover="Fever.displayHelp(this, 'sparks');"></span>
			</li>
			<li class="<?php e(($this->prefs['ui']['section'] == 3 || $this->prefs['ui']['group_id']) ? 'droppable' : ''); ?><?php e(($this->prefs['ui']['section'] == 4) ? ' has-focus' : ''); ?>" id="section-4">
				<form method="get" action="<?php e(errors_url('./'));?>" id="search">
					<input type="hidden" name="ui[section]" value="4" />
					<a href="?ui[section]=4" onclick="return Fever.Reader.loadSection(4);"><i class="icon search"></i></a>
					<span class="search q"><input type="text" name="ui[search]" id="q" tabindex="1" value="<?php e(quote($this->prefs['ui']['search']))?>" /></span>
					<span id="clear-search" class="btn cancel search<?php e(!empty($this->prefs['ui']['search']) ? ' clear' : ''); ?>"></span>
				</form>
				<div id="remove">
					<a><i class="icon trash"></i> Remove from <span id="remove-from"></span></a>
				</div>
			</li>
		</ul>

		<s class="box"><u><u></u></u><i><i></i></i><b><b></b></b></s>
