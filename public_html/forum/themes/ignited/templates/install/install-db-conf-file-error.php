					<h2>Database Credentials Setup Error</h2>
					<div class="board-dialog-details">
						<p>
							The database configuration file (<?=APPPATH . 'config/database.php';?>) is not
							writable.
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