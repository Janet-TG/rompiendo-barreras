// routes/videos.routes.js

const express = require('express');
const router  = express.Router();
const ctrl    = require('../controllers/videos.controller');
const { verificarToken, soloAdmin } = require('../middleware/auth');

router.use(verificarToken);

// Rutas específicas ANTES de /:id para que Express no las confunda
router.get('/mis-videos', ctrl.misVideos);
router.post('/asignar',   soloAdmin, ctrl.asignar);

router.get('/',     ctrl.listar);
router.post('/',    ctrl.crear);
router.get('/:id',  ctrl.obtener);
router.put('/:id',  ctrl.actualizar);
router.delete('/:id', ctrl.eliminar);

module.exports = router;
