TYPO3.jQuery( document ).ready(function( $ )
	{
		$('#t3-login-username-section').addClass('input-group')
		$('#t3-login-username-section').append('<div class="input-group-btn"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-users"></i> <span class="caret"></span></button><ul class="dropdown-menu dropdown-menu-right" role="menu" id="easylogin-userlist"></ul></div>');

		$.each(
			TYPO3.settings.easylogin.users,
			function() {
				$('#easylogin-userlist').append('<li><a href="#" data-username="' + this.username + '" data-password="' + this.password + '"><i class="fa fa-user"></i>' + this.text + '</a></li>')
			}
		);

		$('#easylogin-userlist a').on(
			'click',
			function() {
				var username = $(this).attr('data-username');
				var password = $(this).attr('data-password');
				$('#t3-username').val(username);
				$('#t3-password').val(password);
				$('#t3-login-submit').click();
			}
		);
	}
);