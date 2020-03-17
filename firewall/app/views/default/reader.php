<?php $this->render('reader/header');?>

			<div id="fluid">
				<div id="groups-container">
					<div id="groups" class="box">
<?php $this->render('reader/groups');?>
					</div><!-- #groups -->
					<div id="update-container">
<?php $this->render('manage/update');?>
					</div><!-- #update-container -->
					<div id="status">
						<div id="refreshed" class="status">
							<div class="content">
								<a href="./?refresh&amp;force" target="refresh" onclick="return Fever.Reader.refresh(1);" class="btn refresh">Refresh</a>
								Last refreshed <strong id="total-feeds"><?php e($this->total_feeds);?></strong> <?php e(pluralize($this->total_feeds, 'feed', false))?>
								<strong id="last-refresh" class="timestamp ago-<?php e($this->last_refreshed_on_time); ?>000"><?php e(ago($this->last_refreshed_on_time)); ?></strong>
							</div>
							<s></s>
						</div><!-- #refreshed -->
						<div id="refreshing">
							<div class="status">
								<div class="content">
									<span id="refreshing-feed">Refreshing <strong><i class="favicon icon"></i> feeds</strong></span>
									<a href="about:blank" target="refresh" onclick="return Fever.Reader.cancelRefresh(1);" class="btn cancel">Cancel</a>
								</div>
								<s></s>
							</div>

							<div class="thermometer">
								<div class="groove">
									<div id="mercury">
										<div class="label">
											<strong id="x-feeds">0</strong> of
											<span id="y-feeds"><?php e($this->total_feeds);?></span>
										</div>
									</div><!-- #mercury -->
								</div><!-- .groove -->
								<u></u><b></b>
							</div><!-- .thermometer -->
						</div><!-- #refreshing -->
					</div><!-- #status -->
				</div><!-- #groups-container -->

				<div id="feeds-container">
					<div id="feeds" class="box">
						<div class="content">
							<div id="feeds-scroller-container">
								<div id="feeds-scroller">
<?php include($this->view_file('reader/feeds')); ?>
								</div><!-- #feeds-scroller -->
							</div><!-- #feeds-scroller-container -->
						</div><!-- .content -->
						<s class="box"><u><u></u></u><i><i></i></i><b><b></b></b></s>
					</div><!-- #feeds -->

					<ul id="feeds-alpha">
<?php include($this->view_file('reader/feeds-alpha')); ?>
					</ul><!-- #feeds-alpha -->
				</div><!-- #feeds-container -->
			</div><!-- #fluid -->
			</div><!-- .container -->
		</div><!-- #fixed -->

		<div class="container">
			<div id="content-container">
<?php if ($this->prefs['ui']['section']):?>
<?php $this->render('reader/items');?>
<?php else:?>
<?php $this->render('reader/links');?>
<?php endif;?>
			</div><!-- #content-container -->
		</div><!-- .container -->

<?php $this->render('reader/footer');?>