				<div class="board-notification board-notification-dialog board-notification-bad">
					Delete this install script when you are finished!
				</div>
				<div class="board-dialog" id="board-installer-main" style="display:none;">

				</div>
				<div class="board-dialog" id="board-installer-progress">
					<h2>Loading...</h2>
					<div style="text-align:center; height:48px; line-height:48px;">
						<img src="{BOARD_THEME_IMG}ajax/loader.gif" style="vertical-align:middle;" />
					</div>
					<div style="text-align:center; height:24px; line-height:24px;">Loading...</div>
				</div>
				<script type="text/javascript">
				$(document).ready(function(){
					_load('intro', { challenge: '{CHALLENGE}' });
				});
				/**
				 * Loads a page into the installer wizard.
				 */
				function _load(page, data) {
					// Clear the box, put in loader thingy.
					$('#board-installer-main').hide();
					$('#board-installer-progress').show();
					$('#board-installer-main').html('');
					// Do an ajax request, put the result into the big box.
					$.post('<?=site_url('install/');?>/' + page.toString(), data, function(data) {
						$('#board-installer-main').html(data);
						$('#board-installer-progress').hide();
						$('#board-installer-main').show();
					});
				}
				</script>