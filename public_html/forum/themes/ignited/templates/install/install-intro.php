					<h2>Installation</h2>
					<div class="board-dialog-details">
						<p>
							Welcome to the installation process of IgniteBB! This is the beginning point of
							your journey with IgniteBB.
						</p>
						<p>
							This installation process will take you over several steps, beginning with
							a Database setup, after which your settings will be tested. If this succeeds, you
							will be prompted to create an admin account and set up some board information.
						</p>
						<p>
							If parts of the installation process are already complete, such as the connection
							settings already existing and being valid, then the installer will skip those
							steps.
						</p>
						<p>
							To avoid possible issues, please make sure of the following;
						</p>
						<ul>
							<li>That the database you wish to use already exists, along with the users.</li>
							<li>That the files in the IgniteBB Configuration folder are writable.</li>
							<li>That you have an ample supply of beer or any other suitable beverage for your own enjoyment.</li>
						</ul>
						<p>
							The installer has detected the following about your current progress in the setup
							process. Please note that this is not 100% accurate, and is a best guess tool.
						</p>
						<ul>
							{WRITEABLE}
							{DBCONFSTATE}
							{DBSTATE}
							{ADMINSTATE}
							{BOARDSTATE}
						</ul>
						<div class="field">
							<?
							if(is_writeable(APPPATH . 'config/database.php')) {
								echo form_submit('next', 'Continue', 'onclick="_load(\'conf\', { challenge: \'{CHALLENGE}\' });"');
							} else {
								?>
							You cannot proceed with the installation until the 
							<img src="themes/ignited/img/icons/16x16/exclamation.png" style="vertical-align:middle;" alt="Critical Error" />
							critical errors	are fixed.
								<?php
							}
							?>
						</div>
					</div>