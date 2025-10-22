const swaggerJsdoc = require('swagger-jsdoc');

const options = {
  definition: {
    openapi: '3.0.0',
    info: {
      title: 'Billetera Virtual - API REST',
      version: '1.0.0',
      description: 'API REST para gestión de billetera virtual. Interfaz con servicios SOAP de ePayco.',
      contact: {
        name: 'BilleteraVirtual Support',
        url: 'https://example.com'
      }
    },
    servers: [
      {
        url: 'http://localhost:3000',
        description: 'Desarrollo local'
      },
      {
        url: 'http://localhost/api',
        description: 'Producción'
      }
    ],
    components: {
      schemas: {
        RegistroClienteRequest: {
          type: 'object',
          required: ['tipoDocumento', 'numeroDocumento', 'nombres', 'apellidos', 'email', 'celular'],
          properties: {
            tipoDocumento: {
              type: 'string',
              example: 'CC',
              description: 'Tipo de documento (CC, CE, etc)'
            },
            numeroDocumento: {
              type: 'string',
              example: '1234567890',
              description: 'Número de documento único'
            },
            nombres: {
              type: 'string',
              example: 'Juan',
              description: 'Nombres del cliente'
            },
            apellidos: {
              type: 'string',
              example: 'Pérez',
              description: 'Apellidos del cliente'
            },
            email: {
              type: 'string',
              format: 'email',
              example: 'juan@example.com',
              description: 'Email del cliente'
            },
            celular: {
              type: 'string',
              example: '3001234567',
              description: 'Teléfono celular del cliente'
            }
          }
        },
        RegistroClienteResponse: {
          type: 'object',
          properties: {
            cod_error: {
              type: 'integer',
              example: 0,
              description: 'Código de error (0 = éxito)'
            },
            message_error: {
              type: 'string',
              example: 'Cliente registrado exitosamente'
            },
            data: {
              type: 'object',
              properties: {
                clienteId: {
                  type: 'integer',
                  example: 1
                },
                documento: {
                  type: 'string',
                  example: '1234567890'
                }
              }
            }
          }
        },
        RecargaBilleteraRequest: {
          type: 'object',
          required: ['documento', 'monto'],
          properties: {
            clienteId: {
              type: 'integer',
              example: 1,
              description: 'ID del cliente (opcional)'
            },
            documento: {
              type: 'string',
              example: '1234567890',
              description: 'Número de documento del cliente'
            },
            celular: {
              type: 'string',
              example: '3001234567',
              description: 'Celular del cliente (opcional)'
            },
            monto: {
              type: 'number',
              example: 50000,
              description: 'Monto a recargar en COP'
            },
            referencia: {
              type: 'string',
              example: 'REF-001',
              description: 'Referencia de la transacción (opcional)'
            }
          }
        },
        RecargaBilleteraResponse: {
          type: 'object',
          properties: {
            cod_error: {
              type: 'integer',
              example: 0
            },
            message_error: {
              type: 'string',
              example: 'Billetera recargada exitosamente'
            },
            data: {
              type: 'object',
              properties: {
                nuevoSaldo: {
                  type: 'number',
                  example: 50000
                },
                transaccionId: {
                  type: 'integer'
                }
              }
            }
          }
        },
        PagarRequest: {
          type: 'object',
          required: ['monto', 'descripcion'],
          properties: {
            clienteId: {
              type: 'integer',
              example: 1,
              description: 'ID del cliente (opcional)'
            },
            monto: {
              type: 'number',
              example: 10000,
              description: 'Monto a pagar en COP'
            },
            descripcion: {
              type: 'string',
              example: 'Pago de servicios',
              description: 'Descripción del pago'
            }
          }
        },
        PagarResponse: {
          type: 'object',
          properties: {
            cod_error: {
              type: 'integer',
              example: 0
            },
            message_error: {
              type: 'string',
              example: 'Pago generado exitosamente'
            },
            data: {
              type: 'object',
              properties: {
                sessionId: {
                  type: 'string',
                  example: 'sess_12345'
                },
                token: {
                  type: 'string',
                  example: 'tok_abcdefg123'
                },
                monto: {
                  type: 'number',
                  example: 10000
                }
              }
            }
          }
        },
        ConfirmarPagoRequest: {
          type: 'object',
          required: ['sessionId', 'token'],
          properties: {
            sessionId: {
              type: 'string',
              example: 'sess_12345',
              description: 'Session ID del pago'
            },
            token: {
              type: 'string',
              example: 'tok_abcdefg123',
              description: 'Token de confirmación'
            }
          }
        },
        ConfirmarPagoResponse: {
          type: 'object',
          properties: {
            cod_error: {
              type: 'integer',
              example: 0
            },
            message_error: {
              type: 'string',
              example: 'Pago confirmado exitosamente'
            },
            data: {
              type: 'object',
              properties: {
                transaccionId: {
                  type: 'integer'
                },
                estado: {
                  type: 'string',
                  example: 'COMPLETADO'
                }
              }
            }
          }
        },
        ConsultarSaldoRequest: {
          type: 'object',
          required: ['clienteId', 'documento', 'celular'],
          properties: {
            clienteId: {
              type: 'integer',
              example: 1,
              description: 'ID del cliente'
            },
            documento: {
              type: 'string',
              example: '1234567890',
              description: 'Número de documento'
            },
            celular: {
              type: 'string',
              example: '3001234567',
              description: 'Teléfono celular'
            }
          }
        },
        ConsultarSaldoResponse: {
          type: 'object',
          properties: {
            cod_error: {
              type: 'integer',
              example: 0
            },
            message_error: {
              type: 'string',
              example: 'Saldo consultado exitosamente'
            },
            data: {
              type: 'object',
              properties: {
                saldo: {
                  type: 'number',
                  example: 50000
                },
                clienteId: {
                  type: 'integer',
                  example: 1
                }
              }
            }
          }
        },
        ErrorResponse: {
          type: 'object',
          properties: {
            cod_error: {
              type: 'integer',
              example: 1,
              description: 'Código de error (> 0 indica error)'
            },
            message_error: {
              type: 'string',
              example: 'Error en la solicitud'
            }
          }
        }
      }
    }
  },
  apis: ['./src/routes/*.js']
};

const swaggerSpec = swaggerJsdoc(options);

module.exports = swaggerSpec;
