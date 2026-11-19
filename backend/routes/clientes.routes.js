// routes/clientes.routes.js
// Todas las rutas requieren token JWT válido

const express    = require('express');
const router     = express.Router();
const ctrl       = require('../controllers/clientes.controller');
const { verificarToken } = require('../middleware/auth');

router.use(verificarToken); // Aplica a todas las rutas de este archivo

// GET    /api/clientes        → lista todos
// POST   /api/clientes        → crea nuevo (usa el stored procedure sp_registrar_nuevo_cliente)
// GET    /api/clientes/:id    → obtiene uno
// PUT    /api/clientes/:id    → actualiza
// DELETE /api/clientes/:id    → elimina

router.get('/mi-perfil', ctrl.miPerfil); // antes de /:id para evitar conflicto de rutas
router.get('/',     ctrl.listar);
router.post('/',    ctrl.crear);
router.get('/:id',  ctrl.obtener);
router.put('/:id',  ctrl.actualizar);
router.delete('/:id', ctrl.eliminar);

module.exports = router;
