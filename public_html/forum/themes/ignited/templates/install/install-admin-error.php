					<h2>Administrator Account Setup Error</h2>
					<div class="board-dialog-details">
						<p>
							The administrative account could not be created due to an unknown error.
						</p>
						<div class="field">
							<?=form_submit('next', 'Continue', 'onclick="_load(\'admin\', {
									email : \'{EMAIL}\',
									display_name : \'{DISPLAYNAME}\',
									crAdm : 1
								});"');?>
						</div>
					</div>