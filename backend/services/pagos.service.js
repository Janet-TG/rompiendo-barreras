// services/pagos.service.js
// Simulación de pagos con PayPal Sandbox.
// Al registrar un pago exitoso, el trigger trg_pago_exitoso actualiza automáticamente
// la suscripción del usuario a 'premium'.

const db = require('../db');

async function obtenerTodos() {
  const result = await db.query(`
    SELECT
      p.id_pago,
      u.nombre,
      u.email,
      p.monto,
      p.metodo,
      p.fecha
    FROM pagos p
    LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
    ORDER BY p.fecha DESC
  `);
  return result.rows;
}

async function registrar({ id_usuario, monto, metodo = 'paypal_sandbox' }) {
  // Inserta el pago — el trigger de PostgreSQL se encarga de actualizar la suscripción
  const result = await db.query(
    'INSERT INTO pagos (id_usuario, monto, metodo) VALUES ($1, $2, $3) RETURNING *',
    [id_usuario, monto, metodo]
  );
  return result.rows[0];
}

module.exports = { obtenerTodos, registrar };
