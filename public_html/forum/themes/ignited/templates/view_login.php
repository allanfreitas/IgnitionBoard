				<div class="board-dialog-small" id="board-login-main">
					<h2>Login</h2>
					<div class="board-dialog-details">
						<?=form_open("login/", "onsubmit=\"return login();\"");?>
							<fieldset>
								<div class="field">
									<label for="email">E-mail</label>
									<?=form_input('email', '', 'id="email"');?>
								</div>
								<div class="field">
									<label for="password">Password</label>
									<?=form_password('password', '', 'id="password"');?>
								</div>
								<div class="field">
									<label for="password">Remember Me!</label>
									<?=form_checkbox('remember', '1', FALSE, 'id="remember"');?>
								</div>
								<div class="field">
									<?=form_submit('submit', 'Login');?>
								</div>
								<div class="field">
									<p>Alternatively, register <?=anchor('register', 'here'); ?>.</p>
								</div>
							</fieldset>
						<?=form_close();?>
					</div>
				</div>
				<div class="board-dialog-small" id="board-login-progress" style="display:none;">
					<h2>Login</h2>
					<div style="text-align:center; height:48px; line-height:48px;">
						<img src="{BOARD_THEME_IMG}ajax/loader.gif" style="vertical-align:middle;" />
					</div>
					<div style="text-align:center; height:24px; line-height:24px;">Loading...</div>
				</div>
				<script type="text/javascript">
					// Register an error handler.
					$('#board-login-main').ajaxError(function(){
						// Change how it looks, display an error, switch views.
						$('#board-login-main').removeClass('board-dialog-small').addClass('board-dialog');
						$(this).html('<h2>Login</h2><p>There was an error when trying to log in.</p><p><?=anchor('login', 'Click here to try again'); ?>.</p>');
						$('#board-login-progress').hide();
						$('#board-login-main').show();
					});
					// Login form submit, go go go!
					function login() {
						// Hide forms and the lot.
						$('#board-login-progress').show();
						$('#board-login-main').hide();
						// Set up POST params.
						var params = {
							challenge: '{CHALLENGE}',
							email: $('#email').val(),
							password: $.hash('{KEY}' + $.hash($('#password').val())),
							remember: $('#remember:checked').val()
						};
						// Send.
						$.post('<?=site_url('login/validate');?>', params, function(result) {
							// Put result into box.
							$('#board-login-main').html(result);
							$('#board-login-progress').hide();
							$('#board-login-main').show();
						});
						return false;
					}
				</script>
