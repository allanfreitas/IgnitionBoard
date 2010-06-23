					<h2>Board Configuration Setup</h2>
					<div class="board-dialog-details">
						<?=validation_errors(); ?>
						<?=form_open('', 'onsubmit="return false;"'); // Prevent submit, as this reloads page. ?>
						<fieldset>
							<legend>Board Configuration</legend>
							<div class="field">
								<label for="board_name">Board Name</label>
								<?=form_input('board_name', set_value('board_name'));?>
								<div>This is the name of your board and is displayed in the title and header.</div>
							</div>
							<div class="field">
								<?=form_submit('next', 'Continue', 'onclick="_load(\'admin\', {
										challenge: \'{CHALLENGE}\',
										board_name : $(\'input[name=board_name]\').val(),
										crBrd : 1
									});"');?>
							</div>
						</fieldset>
						<?=form_close();?>
					</div>