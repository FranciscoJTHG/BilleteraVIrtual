const Joi = require('joi');

const registroClienteSchema = Joi.object({
  tipoDocumento: Joi.string().required().trim().messages({
    'string.empty': 'tipoDocumento es requerido',
    'any.required': 'tipoDocumento es requerido'
  }),
  numeroDocumento: Joi.string().required().trim().messages({
    'string.empty': 'numeroDocumento es requerido',
    'any.required': 'numeroDocumento es requerido'
  }),
  nombres: Joi.string().required().trim().messages({
    'string.empty': 'nombres es requerido',
    'any.required': 'nombres es requerido'
  }),
  apellidos: Joi.string().required().trim().messages({
    'string.empty': 'apellidos es requerido',
    'any.required': 'apellidos es requerido'
  }),
  email: Joi.string().email().required().trim().messages({
    'string.email': 'email debe ser válido',
    'string.empty': 'email es requerido',
    'any.required': 'email es requerido'
  }),
  celular: Joi.string().required().trim().messages({
    'string.empty': 'celular es requerido',
    'any.required': 'celular es requerido'
  })
});

const recargaBilleteraSchema = Joi.object({
  clienteId: Joi.number().integer().positive().optional().messages({
    'number.base': 'clienteId debe ser un número',
    'number.positive': 'clienteId debe ser positivo'
  }),
  documento: Joi.string().required().trim().messages({
    'string.empty': 'documento es requerido',
    'any.required': 'documento es requerido'
  }),
  celular: Joi.string().optional().trim(),
  monto: Joi.number().positive().required().messages({
    'number.positive': 'monto debe ser mayor a 0',
    'number.base': 'monto debe ser un número',
    'any.required': 'monto es requerido'
  }),
  referencia: Joi.string().optional().trim()
});

const pagarSchema = Joi.object({
  clienteId: Joi.number().integer().positive().optional().messages({
    'number.base': 'clienteId debe ser un número',
    'number.positive': 'clienteId debe ser positivo'
  }),
  monto: Joi.number().positive().required().messages({
    'number.positive': 'monto debe ser mayor a 0',
    'number.base': 'monto debe ser un número',
    'any.required': 'monto es requerido'
  }),
  descripcion: Joi.string().required().trim().messages({
    'string.empty': 'descripcion es requerido',
    'any.required': 'descripcion es requerido'
  })
});

const confirmarPagoSchema = Joi.object({
  sessionId: Joi.string().required().trim().messages({
    'string.empty': 'sessionId es requerido',
    'any.required': 'sessionId es requerido'
  }),
  token: Joi.string().required().trim().messages({
    'string.empty': 'token es requerido',
    'any.required': 'token es requerido'
  })
});

const consultarSaldoSchema = Joi.object({
  clienteId: Joi.number().integer().positive().required().messages({
    'number.base': 'clienteId debe ser un número',
    'number.positive': 'clienteId debe ser positivo',
    'any.required': 'clienteId es requerido'
  }),
  documento: Joi.string().required().trim().messages({
    'string.empty': 'documento es requerido',
    'any.required': 'documento es requerido'
  }),
  celular: Joi.string().required().trim().messages({
    'string.empty': 'celular es requerido',
    'any.required': 'celular es requerido'
  })
});

module.exports = {
  registroClienteSchema,
  recargaBilleteraSchema,
  pagarSchema,
  confirmarPagoSchema,
  consultarSaldoSchema
};
