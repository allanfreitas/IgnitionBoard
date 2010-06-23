				<div class="reg_form">
					<h2>Register</h2>
					<div class="board-dialog-details">
						<?=validation_errors();?>
						<?=form_open("auth/create_user");?>
							<fieldset>
								<div class="field">
									<label for="email">E-mail</label>
									<?=form_input('email', set_value('email'));?>
									<div>This will be used for login</div>
								</div>
								<div class="field">
									<label for="password">Password</label>
									<?=form_password('password');?>
									<div>Alphanumeric - 6-32 characters</div>
								</div>
								<div class="field">
									<label for="password_confirm">Password confirmation</label>
									<?=form_password('password_confirm');?>
								</div>
								<div class="field">
									<label for="display_name">Desired display name</label>
									<?=form_input('display_name', set_value('display_name'));?>
									<div>This is the name others see</div>
								</div>
								<div class="field">
									<?=form_submit('submit', 'Register');?>
								</div>
								<div class="field">
									<p>Alternatively, login <?= anchor('auth', 'here'); ?>.</p>
								</div>
							</fieldset>
						</form>
						<div class="clear"></div>
					</div>
				</div>
