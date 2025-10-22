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

/**
 * @swagger
 * /wallet/registro-cliente:
 *   post:
 *     summary: Registrar nuevo cliente
 *     description: Crea un nuevo cliente en el sistema con información personal y contacto
 *     tags:
 *       - Clientes
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/RegistroClienteRequest'
 *     responses:
 *       200:
 *         description: Cliente registrado exitosamente
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/RegistroClienteResponse'
 *       400:
 *         description: Datos inválidos o cliente ya existe
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/ErrorResponse'
 *       500:
 *         description: Error interno del servidor
 */
router.post(
  '/registro-cliente',
  validate(registroClienteSchema),
  walletController.registroCliente
);

/**
 * @swagger
 * /wallet/recarga-billetera:
 *   post:
 *     summary: Recargar billetera
 *     description: Incrementa el saldo de la billetera del cliente con el monto especificado
 *     tags:
 *       - Transacciones
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/RecargaBilleteraRequest'
 *     responses:
 *       200:
 *         description: Billetera recargada exitosamente
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/RecargaBilleteraResponse'
 *       400:
 *         description: Datos inválidos o cliente no encontrado
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/ErrorResponse'
 *       500:
 *         description: Error interno del servidor
 */
router.post(
  '/recarga-billetera',
  validate(recargaBilleteraSchema),
  walletController.recargaBilletera
);

/**
 * @swagger
 * /wallet/pagar:
 *   post:
 *     summary: Generar token de pago
 *     description: Genera un token y sesión para procesar un pago
 *     tags:
 *       - Transacciones
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/PagarRequest'
 *     responses:
 *       200:
 *         description: Token de pago generado exitosamente
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/PagarResponse'
 *       400:
 *         description: Datos inválidos
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/ErrorResponse'
 *       500:
 *         description: Error interno del servidor
 */
router.post(
  '/pagar',
  validate(pagarSchema),
  walletController.pagar
);

/**
 * @swagger
 * /wallet/confirmar-pago:
 *   post:
 *     summary: Confirmar pago
 *     description: Valida y confirma un pago usando el sessionId y token generados en /pagar
 *     tags:
 *       - Transacciones
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/ConfirmarPagoRequest'
 *     responses:
 *       200:
 *         description: Pago confirmado exitosamente
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/ConfirmarPagoResponse'
 *       400:
 *         description: Datos inválidos o pago rechazado
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/ErrorResponse'
 *       500:
 *         description: Error interno del servidor
 */
router.post(
  '/confirmar-pago',
  validate(confirmarPagoSchema),
  walletController.confirmarPago
);

/**
 * @swagger
 * /wallet/consultar-saldo:
 *   get:
 *     summary: Consultar saldo de billetera
 *     description: Obtiene el saldo actual de la billetera del cliente
 *     tags:
 *       - Consultas
 *     parameters:
 *       - name: clienteId
 *         in: query
 *         description: ID del cliente
 *         required: true
 *         schema:
 *           type: integer
 *           example: 1
 *       - name: documento
 *         in: query
 *         description: Número de documento del cliente
 *         required: true
 *         schema:
 *           type: string
 *           example: "1234567890"
 *       - name: celular
 *         in: query
 *         description: Teléfono celular del cliente
 *         required: true
 *         schema:
 *           type: string
 *           example: "3001234567"
 *     responses:
 *       200:
 *         description: Saldo consultado exitosamente
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/ConsultarSaldoResponse'
 *       400:
 *         description: Datos inválidos o cliente no encontrado
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/ErrorResponse'
 *       500:
 *         description: Error interno del servidor
 */
router.get(
  '/consultar-saldo',
  validate(consultarSaldoSchema),
  walletController.consultarSaldo
);

module.exports = router;
