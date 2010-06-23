					<h2>Database Credentials Setup</h2>
					<div class="board-dialog-details">
						<?=validation_errors(); ?>
						<?=form_open('', 'onsubmit="return false;"'); // Prevent submit, as this reloads page. ?>
						<fieldset>
							<legend>Database Credentials</legend>
							<div class="field">
								<label for="dbhost">Database Hostname</label>
								<?=form_input('dbhost', '{HOSTNAME}');?>
								<div>The hostname of your database. If unsure, leave as localhost.</div>
							</div>
							<div class="field">
								<label for="dbuser">Database Username</label>
								<?= form_input('dbuser', '{USERNAME}');?>
								<div>The username to connect to your database.</div>
							</div>
							<div class="field">
								<label for="dbpass">Database Password</label>
								<?=form_password('dbpass');?>
								<div>The password to connect to your database.</div>
							</div>
							<div class="field">
								<label for="dbname">Database Name</label>
								<?=form_input('dbname', '{DBNAME}');?>
								<div>The name of the database to install the boards in.</div>
							</div>
							<div class="field">
								<label for="dbprefix">Table Prefix</label>
								<?=form_input('dbprefix', '{DBPREFIX}');?>
								<div>An optional prefix for the IgniteBB tables in this database.</div>
							</div>
							<div class="field">
								<?=form_submit('next','Continue', 'onclick="_load(\'conf\', {
									dbhost : $(\'input[name=dbhost]\').val(),
									dbuser : $(\'input[name=dbuser]\').val(),
									dbpass : $(\'input[name=dbpass]\').val(),
									dbname : $(\'input[name=dbname]\').val(),
									dbprefix : $(\'input[name=dbprefix]\').val(),
									challenge : \'{CHALLENGE}\',
									crConf : 1
								});"');?>
							</div>
						</fieldset>
						<?=form_close();?>
					</div>