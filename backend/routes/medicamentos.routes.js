// routes/medicamentos.routes.js

const express = require('express');
const router  = express.Router();
const ctrl    = require('../controllers/medicamentos.controller');
const { verificarToken, soloAdmin } = require('../middleware/auth');

router.use(verificarToken);

// IMPORTANTE: rutas específicas ANTES de /:id para que Express no las confunda
// Devuelve solo los medicamentos asignados al cliente autenticado (app móvil)
router.get('/mis-medicamentos', ctrl.misMedicamentos);

// Admin asigna un medicamento a un cliente
router.post('/asignar', soloAdmin, ctrl.asignar);

router.get('/',       ctrl.listar);
router.post('/',      ctrl.crear);
router.get('/:id',    ctrl.obtener);
router.put('/:id',    ctrl.actualizar);
router.delete('/:id', ctrl.eliminar);

module.exports = router;
