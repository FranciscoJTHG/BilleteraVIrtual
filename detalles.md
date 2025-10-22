Se necesita crear un servicio soap y servicio rest (puente entre el cliente y el soap) que simule una billetera virtual. 
El sistema debe poder registrar un cliente, cargar dinero a la billetera, hacer una compra con un código de confirmación y consultar el saldo de la billetera. 
Funcionalidades
Registro Clientes: Crear un método llamado registro cliente donde reciba los 
siguientes parámetros 
• Documento 
• Nombres,
• Email,
• Celular 
Se debe registrar el usuario, todos los campos son requeridos, el soap debe dar como resultado un mensaje con su respectivo código de error y mensaje de éxito o fallo. 
Recarga Billetera: 
Se debe permitir cargar la billetera enviando el documento, número de celular y valor, se debe responder un mensaje de éxito o fallo. 
Pagar 
La billetera con saldo debe permitir pagar una compra enviada, pero para descontar el saldo el sistema deberá́ generar un token de 6 dígitos el cual deben 
de confirmar enviando un id de sesión y el token. Se sugiere enviar el token al email del usuario registrado. 
Si todo es correcto se genera un mensaje y una respuesta diciendo que se ha enviado un correo más el id de sesión que debe ser usado en la confirmación de la compra. 
Confirmar Pago 
Esta función valida el id de sesión generado en la compra, valida el token enviado al usuario al correo, si es correcto el dinero se descuenta de la billetera y se genera el respectivo mensaje de éxito o fallo. 
Consultar Saldo 
Para consultar el saldo se debe enviar el documento y numero de celular los dos valores deben coincidir. 
Notas:
Todas las respuestas tanto del soap y del rest deben de manejar una misma 
estructura se sugiere como ejemplo la siguiente estructura: 
success:true o false (dice si el resultado de la operación tuvo éxito o no) cod_error: (Código con el error si es éxito se sugiere enviar 00 sino el código de error correspondiente)
message_error: (Mensaje explicativo del código de error)
data (Array u objetos con las respuestas). 
El único que puede acceder a la base de datos y es el serivcio soap este servicio es el único que se podrá conectar a la bd se valorara el resultado si hace con un orm como doctrine. 
El servicio rest tiene los mismos métodos que el soap y debe de ser el puente entre el cliente y la base de datos consumiendo el soap generado. 
es necesario hacer un cliente web con enviar los parámetros por postman es suficiente. 
Para crear el servicio Soap se puede usar frameworks como symfony o laravel u otro framework, si el rest se hace usando lumen o express con node se valorará mejor estos conocimientos. 
La base de datos para guardar el registro de cliente y el saldo de la wallet puede ser mysql o mongo. 
Tiempo de entrega 
El tiempo de entrega máximo es de 48 horas.
Montar el proyecto en git como sugerencia hacer commit cada q se vaya terminando una funcionalidad. 
