// services/clientes.service.js
// CRUD de clientes. Los clientes siempre tienen un usuario asociado (rol='cliente').
// Usamos el stored procedure sp_registrar_nuevo_cliente para crear cliente + usuario + suscripcion en una sola operación.

const bcrypt = require('bcryptjs');
const db     = require('../db');

async function obtenerTodos() {
  // JOIN con usuarios para traer nombre y email del cliente
  const result = await db.query(`
    SELECT
      c.id_cliente,
      u.nombre,
      u.email,
      c.diagnostico,
      c.telefono,
      u.created_at
    FROM clientes c
    JOIN usuarios u ON u.id_usuario = c.id_usuario
    ORDER BY c.id_cliente DESC
  `);
  return result.rows;
}

async function obtenerPorId(id) {
  const result = await db.query(`
    SELECT
      c.id_cliente,
      c.id_usuario,
      u.nombre,
      u.email,
      c.diagnostico,
      c.telefono,
      u.created_at
    FROM clientes c
    JOIN usuarios u ON u.id_usuario = c.id_usuario
    WHERE c.id_cliente = $1
  `, [id]);

  if (result.rows.length === 0) throw new Error('Cliente no encontrado.');
  return result.rows[0];
}

async function crear({ nombre, email, password, diagnostico, telefono }) {
  // Hashea la contraseña antes de enviarla al procedure
  const hash = await bcrypt.hash(password, 10);

  // Llama al stored procedure que crea usuario + cliente + suscripcion estandar
  await db.query(
    'CALL sp_registrar_nuevo_cliente($1, $2, $3, $4, $5)',
    [nombre, email, hash, diagnostico, telefono]
  );

  // Regresa el cliente recién creado para confirmar al frontend
  const nuevo = await db.query(`
    SELECT c.id_cliente, u.nombre, u.email, c.diagnostico, c.telefono
    FROM clientes c
    JOIN usuarios u ON u.id_usuario = c.id_usuario
    WHERE u.email = $1
  `, [email]);

  return nuevo.rows[0];
}

async function actualizar(id, { nombre, email, diagnostico, telefono }) {
  // Actualizamos los campos del usuario y del cliente por separado
  // Primero obtenemos el id_usuario del cliente
  const rel = await db.query(
    'SELECT id_usuario FROM clientes WHERE id_cliente = $1',
    [id]
  );
  if (rel.rows.length === 0) throw new Error('Cliente no encontrado.');
  const id_usuario = rel.rows[0].id_usuario;

  await db.query(
    'UPDATE usuarios SET nombre = $1, email = $2 WHERE id_usuario = $3',
    [nombre, email, id_usuario]
  );

  await db.query(
    'UPDATE clientes SET diagnostico = $1, telefono = $2 WHERE id_cliente = $3',
    [diagnostico, telefono, id]
  );

  return obtenerPorId(id);
}

async function eliminar(id) {
  // El CASCADE en la FK de clientes.id_usuario elimina el cliente al borrar el usuario
  const rel = await db.query(
    'SELECT id_usuario FROM clientes WHERE id_cliente = $1',
    [id]
  );
  if (rel.rows.length === 0) throw new Error('Cliente no encontrado.');

  await db.query('DELETE FROM usuarios WHERE id_usuario = $1', [rel.rows[0].id_usuario]);
  return { mensaje: 'Cliente eliminado correctamente.' };
}

// Devuelve el perfil del cliente autenticado usando su id_usuario del JWT
async function obtenerPorUsuario(id_usuario) {
  const result = await db.query(`
    SELECT
      c.id_cliente,
      c.id_usuario,
      u.nombre,
      u.email,
      c.diagnostico,
      c.telefono
    FROM clientes c
    JOIN usuarios u ON u.id_usuario = c.id_usuario
    WHERE c.id_usuario = $1
  `, [id_usuario]);

  if (result.rows.length === 0) throw new Error('Perfil de cliente no encontrado.');
  return result.rows[0];
}

module.exports = { obtenerTodos, obtenerPorId, obtenerPorUsuario, crear, actualizar, eliminar };
