const soapClient = require('../services/soapClient');

const walletController = {
  registroCliente: async (req, res, next) => {
    try {
      const {
        tipoDocumento,
        numeroDocumento,
        nombres,
        apellidos,
        email,
        celular
      } = req.validatedData;

      const result = await soapClient.registroCliente(
        tipoDocumento,
        numeroDocumento,
        nombres,
        apellidos,
        email,
        celular
      );

      res.json(result);
    } catch (error) {
      next(error);
    }
  },

  recargaBilletera: async (req, res, next) => {
    try {
      const {
        clienteId,
        documento,
        celular,
        monto,
        referencia
      } = req.validatedData;

      const result = await soapClient.recargaBilletera(
        clienteId || 0,
        documento,
        celular || '',
        monto,
        referencia || ''
      );

      res.json(result);
    } catch (error) {
      next(error);
    }
  },

  pagar: async (req, res, next) => {
    try {
      const {
        clienteId,
        monto,
        descripcion
      } = req.validatedData;

      const result = await soapClient.pagar(
        clienteId || 0,
        monto,
        descripcion
      );

      res.json(result);
    } catch (error) {
      next(error);
    }
  },

  confirmarPago: async (req, res, next) => {
    try {
      const {
        sessionId,
        token
      } = req.validatedData;

      const result = await soapClient.confirmarPago(
        sessionId,
        token
      );

      res.json(result);
    } catch (error) {
      next(error);
    }
  },

  consultarSaldo: async (req, res, next) => {
    try {
      const {
        clienteId,
        documento,
        celular
      } = req.validatedData;

      const result = await soapClient.consultarSaldo(
        clienteId || 0,
        documento,
        celular || ''
      );

      res.json(result);
    } catch (error) {
      next(error);
    }
  }
};

module.exports = walletController;
