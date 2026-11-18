<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Web HireLevel Completa</title>

    <link rel="stylesheet" href="assets/css/bootstrap.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="#">
                <img src="assets/img/logo.png" alt="Hi Logo" width="70"> 
                
                <span class="logo-text">
                    <small class="text-secondary d-block">Rompiendo</small>
                    <span class="text-hirelevel">Barreras</span>
                </span>
            </a>    
        
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav align-items-center">
                            <li class="nav-item d-flex gap-2">
                                <a class="btn-nav-rojo" id="btn-register" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">registrarse</a>
                                <a class="btn-nav-morado" id="btn-login" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">iniciar sesion</a>
                            </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <section class="hero-section">

            <div class="row g-0">

                <!-- columna roja -->
                <div class="col-lg-6 col-md-12 card-roja">
                    <!-- texto centrado con flexbox -->
                    <div class="hero-content">
                        <p class="hero-subheader">Clínica</p>
                        <h1 class="hero-title">Clínica de<br>Rehabilitación Motriz</h1>
                        <p class="hero-text">Atendemos personas con discapacidad motriz ofreciendo terapia física, programas de movilidad y apoyo integral para mejorar su independencia.</p>
                    </div>
                </div>

                <!-- columna azul -->
                <div class="col-lg-6 col-md-12 card-azul">
                    <!-- centrado con flexbox -->
                    <div class="hero-content">
                        <p class="hero-subheader">Servicios</p>
                        <h1 class="hero-title">Terapia y<br>Soporte</h1>
                        <p class="hero-text">Programas personalizados: fisioterapia, hidroterapia, asesoría de ayudas técnicas y planes de inclusión laboral y social.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="base-info">
        <div class="container text-center">
              <div class="hi-logo my-4">
                  <img src="assets/img/logo.png" alt="Logo Clínica" width="100">
              </div>
              <h2 class="fs-4 fw-normal">Comprometidos con la movilidad y la inclusión.</h2>
              <p class="text-muted col-lg-8 mx-auto">Nuestra misión es apoyar a personas con discapacidad motriz mediante programas clínicos y comunitarios que fomenten la autonomía y la calidad de vida.</p>
              <a href="#" class="btn-more">Conócenos</a>
        </div>
    </section>
    
    
    <section class="we-know-people">
        <img src="assets/img/foto_pw1.jpg" alt="Oficina moderna con personas">
        <div class="overlay">
            <h1>01</h1>
            <h2>Atención especializada</h2>
            <p>Equipo multidisciplinario enfocado en rehabilitación motriz, evaluaciones funcionales y planes personalizados para cada paciente.</p>
            <a href="#" class="btn">Saber más</a>
        </div>
    </section>
    
    <section class="services text-center">
        <div class="container">
            <small>SERVICIOS</small>
            <h3 class="fw-bold mb-5">PROGRAMAS CLÍNICOS</h3>
            
            <div class="row g-4"> 
                
                <div class="col-12 col-md-4">
                    <div class="service-box">
                        <img src="assets/img/foto_pw2.jpg" alt="Fisioterapia">
                        <h5>Fisioterapia</h5>
                        <a href="#">VER</a>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="service-box">
                        <img src="assets/img/hidroterapia.jpg" alt="Hidroterapia">
                        <h5>Hidroterapia</h5>
                        <a href="#">VER</a>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="service-box">
                        <img src="assets/img/foto_pw.jpg" alt="Asesoría de ayudas técnicas">
                        <h5>Ayudas Técnicas</h5>
                        <a href="#">VER</a>
                    </div>
                </div>

            </div>
        </div>
        </section>

<!-- Modales de Login y Registro (overlay sobre la portada) -->
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Iniciar sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="loginAlert" class="alert alert-danger" role="alert" style="display:none;"></div>
                <form id="form-login-modal" action="lib/ajax_iniciar_sesion.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" name="mail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="pass" required>
                    </div>
                    <div class="d-grid">
                        <button type="button" id="loginSubmit" class="btn-nav-morado">Ingresar</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <small>¿No tienes cuenta? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Regístrate</a></small>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrarse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="registerAlert" class="alert alert-danger" role="alert" style="display:none;"></div>
                <form id="form-register-modal" action="lib/ajax_register.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de teléfono</label>
                        <input type="tel" class="form-control" name="telefono" placeholder="Ej: 555-1234" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="button" id="registerSubmit" class="btn-nav-rojo">registrarse</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <small>¿Ya tienes cuenta? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Iniciar sesión</a></small>
            </div>
        </div>
    </div>
</div>

<!-- Incluir Bootstrap JS bundle (CDN) para modales -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
// AJAX submit para login
document.addEventListener('DOMContentLoaded', function () {
    var loginForm = document.getElementById('form-login-modal');
    var loginAlert = document.getElementById('loginAlert');
    if (loginForm) {
        // Prevenir que el formulario se envíe por defecto
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        var loginBtn = document.getElementById('loginSubmit');
        if (loginBtn) {
            loginBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                loginAlert.style.display = 'none';
                loginBtn.disabled = true;
                var fd = new FormData(loginForm);
                // usar action relativa
                var url = loginForm.getAttribute('action') || 'lib/ajax_iniciar_sesion.php';
                fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (res) {
                        if (!res.ok) {
                            throw new Error('HTTP ' + res.status);
                        }
                        return res.text();
                    })
                    .then(function (text) {
                        try {
                            var data = JSON.parse(text);
                            if (data.success) {
                                var redirectUrl = data.redirect || 'cliente/';
                                
                                // Cerrar el modal ANTES de redirigir
                                var loginModalEl = document.getElementById('loginModal');
                                if (loginModalEl) {
                                    var loginModal = bootstrap.Modal.getInstance(loginModalEl);
                                    if (loginModal) {
                                        loginModal.hide();
                                    }
                                }
                                
                                // Redirigir después de cerrar el modal
                                setTimeout(function() {
                                    document.location.href = redirectUrl;
                                }, 150);
                            } else {
                                loginAlert.textContent = data.error || 'Error en el inicio de sesión';
                                loginAlert.style.display = 'block';
                                loginBtn.disabled = false;
                            }
                        } catch (parseErr) {
                            loginAlert.textContent = 'Error al procesar respuesta del servidor';
                            loginAlert.style.display = 'block';
                            loginBtn.disabled = false;
                        }
                    }).catch(function (err) {
                        loginAlert.textContent = 'Error en la conexión: ' + err.message;
                        loginAlert.style.display = 'block';
                        loginBtn.disabled = false;
                    });
            });
        }
    }

    // AJAX submit para registro
    var regForm = document.getElementById('form-register-modal');
    var regAlert = document.getElementById('registerAlert');
    if (regForm) {
        // Prevenir que el formulario se envíe por defecto
        regForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        var registerBtn = document.getElementById('registerSubmit');
        if (registerBtn) {
            registerBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                regAlert.style.display = 'none';
                registerBtn.disabled = true;
                var fd = new FormData(regForm);
                var url = regForm.getAttribute('action') || 'lib/ajax_register.php';
                fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            // Cerrar modal de registro, abrir modal de login y mostrar mensaje de éxito
                            try {
                                var regModalEl = document.getElementById('registerModal');
                                var regModal = bootstrap.Modal.getInstance(regModalEl) || new bootstrap.Modal(regModalEl);
                                regModal.hide();
                            } catch (e) {}

                            try {
                                var loginModalEl = document.getElementById('loginModal');
                                var loginModal = bootstrap.Modal.getOrCreateInstance ? bootstrap.Modal.getOrCreateInstance(loginModalEl) : new bootstrap.Modal(loginModalEl);
                                // show success message in loginAlert
                                loginAlert.classList.remove('alert-danger');
                                loginAlert.classList.add('alert-success');
                                loginAlert.textContent = data.message || 'Registro completado. Por favor, inicia sesión.';
                                loginAlert.style.display = 'block';
                                loginModal.show();
                            } catch (e) {}

                            registerBtn.disabled = false;
                        } else {
                            regAlert.textContent = data.error || 'Error en el registro';
                            regAlert.style.display = 'block';
                            registerBtn.disabled = false;
                        }
                    }).catch(function (err) {
                        regAlert.textContent = 'Error en la conexión';
                        regAlert.style.display = 'block';
                        registerBtn.disabled = false;
                    });
            });
        }
    }
});
</script>
</body>
</html>