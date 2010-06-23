					<h2>Administrator Account Setup</h2>
					<div class="board-dialog-details">
						<?=validation_errors(); ?>
						<?=form_open('', 'onsubmit="return false;"'); // Prevent submit, as this reloads page. ?>
						<fieldset>
							<legend>Account Details</legend>
							<div class="field">
								<label for="email">E-Mail Address</label>
								<?=form_input('email', set_value('email'));?>
								<div>This will be used for your login.</div>
							</div>
							<div class="field">
								<label for="password">Password</label>
								<?=form_password('password');?>
								<div>6-32 Characters.</div>
							</div>
							<div class="field">
								<label for="password_conf">Password Confirmation</label>
								<?=form_password('password_conf');?>
								<div>Same as the other password.</div>
							</div>
							<div class="field">
								<label for="display_name">Display Name</label>
								<?=form_input('display_name', set_value('display_name'));?>
								<div>This will be the name other people see.</div>
							</div>
							<div class="field">
								<?=form_submit('next', 'Continue', 'onclick="_load(\'admin\', {
										challenge: \'{CHALLENGE}\',
										email : $(\'input[name=email]\').val(),
										password : $.hash($(\'input[name=password]\').val()),
										password_conf : $.hash($(\'input[name=password_conf]\').val()),
										display_name : $(\'input[name=display_name]\').val(),
										crAdm : 1 
									});"');?>
							</div>
						</fieldset>
						<?=form_close();?>
					</div>