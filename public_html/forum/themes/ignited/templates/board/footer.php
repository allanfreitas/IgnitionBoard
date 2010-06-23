			</div><!-- end board_content -->
		</div>
		<div id="board-footer">
			<hr/>
			<p>
				Copyright &copy; <?php
					$year_start = '2010';
					$year_current = date('Y');
		
					if($year_start == $year_current) {
						echo $year_start;
					} else {
						echo $year_start.' - '.$year_current;
					}
				?> :: Daniel Yates &amp; Dale Emasiri :: IgniteBB<br/>
				This software is licensed and distributed under <?= anchor('http://www.opensource.org/licenses/mit-license.php', 'The MIT License'); ?><br/>
				Built using CodeIgniter by EllisLab :: CodeIgniter's standard license still applies to the system's core
			</p>
		</div>
	</body>
</html>
