// Validación cliente para formularios comunes
document.addEventListener('DOMContentLoaded', function(){
  // Login
  var loginForm = document.getElementById('form-login');
  if (loginForm){
    loginForm.addEventListener('submit', function(e){
      var usuario = document.getElementById('usuario');
      var password = document.getElementById('password');
      if (!usuario.value.trim() || !password.value.trim()){
        e.preventDefault();
        alert('Por favor ingresa usuario y contraseña.');
        usuario.focus();
      }
    });
  }

  // Registro
  var regForm = document.getElementById('form-registro');
  if (regForm){
    regForm.addEventListener('submit', function(e){
      var nombre = document.getElementById('nombre');
      var email = document.getElementById('email');
      var pwd = document.getElementById('password');
      if (!nombre.value.trim()){
        e.preventDefault(); alert('El nombre es requerido.'); nombre.focus(); return;
      }
      var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\\.,;:\s@\"]+\.)+[^<>()[\]\\.,;:\s@\"]{2,})$/i;
      if (!re.test(email.value)){
        e.preventDefault(); alert('Correo inválido.'); email.focus(); return;
      }
      if (pwd.value.length < 6){
        e.preventDefault(); alert('La contraseña debe tener al menos 6 caracteres.'); pwd.focus(); return;
      }
    });
  }

  // Pacientes create/edit
  var pacForm = document.getElementById('form-paciente');
  if (pacForm){
    pacForm.addEventListener('submit', function(e){
      var nombreUsuario = pacForm.querySelector('[name="nom_usr"]');
      var nombrePaciente = pacForm.querySelector('[name="nom_paciente"]');
      var nombre = nombreUsuario || nombrePaciente;
      if (!nombre || !nombre.value.trim()){
        e.preventDefault();
        alert('Nombre de usuario requerido.');
        if (nombre) nombre.focus();
        return;
      }

      var email = pacForm.querySelector('[name="mail"]');
      var pwd = pacForm.querySelector('[name="pass"]');
      if (email || pwd) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\\.,;:\s@\"]+\.)+[^<>()[\]\\.,;:\s@\"]{2,})$/i;
        if (!email || !re.test(email.value)){
          e.preventDefault();
          alert('Correo inválido para el usuario.');
          if (email) email.focus();
          return;
        }
        if (!pwd || pwd.value.length < 6){
          e.preventDefault();
          alert('La contraseña del usuario debe tener al menos 6 caracteres.');
          if (pwd) pwd.focus();
          return;
        }
      }
    });
  }
});