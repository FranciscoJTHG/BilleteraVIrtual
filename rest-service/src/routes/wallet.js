const express = require('express');
const router = express.Router();
const validate = require('../middlewares/validator');
const {
  registroClienteSchema,
  recargaBilleteraSchema,
  pagarSchema,
  confirmarPagoSchema,
  consultarSaldoSchema
} = require('../validators/schemas');
const walletController = require('../controllers/walletController');

router.post(
  '/registro-cliente',
  validate(registroClienteSchema),
  walletController.registroCliente
);

router.post(
  '/recarga-billetera',
  validate(recargaBilleteraSchema),
  walletController.recargaBilletera
);

router.post(
  '/pagar',
  validate(pagarSchema),
  walletController.pagar
);

router.post(
  '/confirmar-pago',
  validate(confirmarPagoSchema),
  walletController.confirmarPago
);

router.get(
  '/consultar-saldo',
  validate(consultarSaldoSchema),
  walletController.consultarSaldo
);

module.exports = router;
