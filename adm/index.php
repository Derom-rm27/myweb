<?PHP
//================================================//
//===========INICIAR SESION==========//
//================================================//
//require "login.php";

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_GET['mensaje'])){
	// Obtener el mensaje de la URL si existe
	$mensaje = urldecode($_GET['mensaje']);
}else{
	$mensaje = "Inicie sesión";
}

?>
<!DOCTYPE html>
<html>
    
<head>
	<title>Inicio de Sesion</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/estilo.css">
	<script language='JavaScript' type='text/javascript' src='js/generax.js'></script>
	</head>
	<!--Coded with love by Mutiullah Samim-->
	<body>
		<div class="container h-100">
		<div class="d-flex justify-content-center h-100">
			<div class="user_card">
				<div class="d-flex justify-content-center">
					<div class="brand_logo_container">
						<img src="https://cdn.freebiesupply.com/logos/large/2x/pinterest-circle-logo-png-transparent.png" class="brand_logo" alt="Logo">
					</div>
				</div>
				<div class="d-flex justify-content-center form_container">
					<form name='sesion' action='login.php' onSubmit='return iniciar();' method='POST'>
						<div id="mensaje" class="d-flex justify-content-center">
							<?php echo $mensaje; ?>
						</div>
						<div class="input-group mb-3">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-user"></i></span>
							</div>
							<input type="text" name="users" class="form-control input_user" value="" placeholder="Nombre Usuario">
						</div>
						<div class="input-group mb-2">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-key"></i></span>
							</div>
							<input type="password" name="pass" class="form-control input_pass" value="" placeholder="Contraseña">
						</div>
						<div class="input-group mb-1">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-key"></i></span>
							</div>
							<img width=100 height=22 src='script/generax.php?img=true'>
						</div>
						<div class="input-group mb-2">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-key"></i></span>
							</div>
							<input type="text" name="clave" class="form-control input_user" value="" placeholder="Escriba Imagen">
						</div>
						<div class="form-group">
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input" id="customControlInline">
								<label class="custom-control-label" for="customControlInline">Recuérdame</label>
							</div>
						</div>
							<div class="d-flex justify-content-center mt-3 login_container">
								<input type="submit" name="button" class="btn login_btn" value="Ingresar">
							</div>
					</form>
				</div>
		
				<div class="mt-4">
					<div class="d-flex justify-content-center links">
						No tengo una cuenta? <a href="#" class="ml-2">Inscribirse</a>
					</div>
					<div class="d-flex justify-content-center links">
						<a href="#">Olvidaste tu contraseña?</a>
					</div>
				</div>
			</div>
		</div>
	</div>



</body>
</html>