<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<body>
		<p>
			<?php echo $email; ?>,
		</p>
		
		<p>
			Someone (presumably you) just requested that your password for MuncieEvents.com be reset 
			so you can log in again. We would just <i>tell</i> you what your password is, but since 
			it's stored in a one-way encrypted format, we actually have no way to figure out what it is.
		</p>
		
		<p>
			Anyway, just click on this link and you'll be prompted to enter in a new password to overwrite 
			your old one.
		</p>
		
		<p>
			<a href="http://MuncieEvents.com/reset_password/<?php echo $user_id; ?>/<?php echo $reset_password_hash; ?>">
				http://MuncieEvents.com/reset_password/<?php echo $user_id; ?>/<?php echo $reset_password_hash; ?>
			</a>
		</p>
		
		<p>
			<strong>NOTE: That link will only work for the rest of <?php echo date('F Y'); ?>.</strong>
			If you need to reset your password in <?php echo date('F', strtotime('+1 month')); ?>, you'll need
			to request another password reset link. That's so if some creep finds this email later on, they 
			won't be able to use it to get into your account. Still, it would be a good idea to delete this 
			email as soon as you've reset your password.
		</p>
		
		<p>
			Love,
			<br /> 
			<a href="http://MuncieEvents.com">Muncie Events</a>
		</p>
	</body>
</html>