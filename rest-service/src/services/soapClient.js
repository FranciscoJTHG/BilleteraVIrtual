const axios = require('axios');
const xml2js = require('xml2js');

const SOAP_URL = process.env.SOAP_URL || 'http://soap:8000/soap';

const buildSoapEnvelope = (method, params) => {
  const paramXml = Object.entries(params)
    .map(([key, value]) => `<${key}>${String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</${key}>`)
    .join('\n    ');

  return `<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="http://epayco.com/wallet">
  <soap:Body>
    <${method} xmlns="http://epayco.com/wallet">
      ${paramXml}
    </${method}>
  </soap:Body>
</soap:Envelope>`;
};

const parseSoapResponse = async (xmlResponse) => {
  try {
    const parser = new xml2js.Parser({ 
      explicitArray: false,
      mergeAttrs: true,
      emptyTag: 'object'
    });
    const result = await parser.parseStringPromise(xmlResponse);

    const soapBody = result['SOAP-ENV:Envelope']?.['SOAP-ENV:Body'] || result['soap:Envelope']?.['soap:Body'];
    const methodResponse = Object.keys(soapBody)[0];
    const responseData = soapBody[methodResponse]['response'];

    return {
      success: responseData.success === 'true' || responseData.success === true,
      cod_error: responseData.cod_error,
      message_error: responseData.message_error,
      data: responseData.data
    };
  } catch (error) {
    throw new Error(`Error parsing SOAP response: ${error.message}`);
  }
};

const callSoapMethod = async (method, params) => {
  try {
    const soapEnvelope = buildSoapEnvelope(method, params);

    const response = await axios.post(SOAP_URL, soapEnvelope, {
      headers: {
        'Content-Type': 'text/xml; charset=utf-8',
        'SOAPAction': method
      },
      timeout: 10000
    });

    return await parseSoapResponse(response.data);
  } catch (error) {
    if (error.response?.data) {
      return await parseSoapResponse(error.response.data);
    }
    throw new Error(`SOAP call failed: ${error.message}`);
  }
};

const soapClient = {
  registroCliente: async (tipoDocumento, numeroDocumento, nombres, apellidos, email, celular) => {
    return callSoapMethod('registroCliente', {
      tipoDocumento,
      numeroDocumento,
      nombres,
      apellidos,
      email,
      celular
    });
  },

  recargaBilletera: async (clienteId, documento, celular, monto, referencia) => {
    return callSoapMethod('recargaBilletera', {
      clienteId,
      documento,
      celular,
      monto,
      referencia
    });
  },

  pagar: async (clienteId, monto, descripcion) => {
    return callSoapMethod('pagar', {
      clienteId,
      monto,
      descripcion
    });
  },

  confirmarPago: async (sessionId, token) => {
    return callSoapMethod('confirmarPago', {
      sessionId,
      token
    });
  },

  consultarSaldo: async (clienteId, documento, celular) => {
    return callSoapMethod('consultarSaldo', {
      clienteId,
      documento,
      celular
    });
  }
};

module.exports = soapClient;
