					<h2>Database Credentials Setup Error</h2>
					<div class="board-dialog-details">
						<p>
							The database connection settings you entered were incorrect.
						</p>
						<div class="field">
							<?=form_submit('next', 'Continue', 'onclick="_load(\'conf\', {
									dbhost : \'{HOSTNAME}\',
									dbuser : \'{USERNAME}\',
									dbname : \'{DBNAME}\',
									dbprefix : \'{DBPREFIX}\',
									challenge : \'{CHALLENGE}\',
									crConf : 1
								});"');?>
						</div>
					</div>