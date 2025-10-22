<?php

namespace App\Service;

use App\Entity\Cliente;
use App\Entity\Billetera;
use App\Entity\Transaccion;
use App\Entity\PagoPendiente;
use App\Repository\ClienteRepository;
use App\Repository\BilleteraRepository;
use App\Repository\TransaccionRepository;
use App\Repository\PagoPendienteRepository;
use App\Constants\ErrorCodes;
use App\DTOs\RegistroClienteDTO;
use App\DTOs\RecargaBilleteraDTO;
use App\DTOs\PagarDTO;
use App\DTOs\ConfirmarPagoDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WalletService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClienteRepository $clienteRepository,
        private BilleteraRepository $billeteraRepository,
        private TransaccionRepository $transaccionRepository,
        private PagoPendienteRepository $pagoPendienteRepository,
        private MailerInterface $mailer,
        private ValidatorInterface $validator,
    ) {}

    public function registroCliente(
        string $tipoDocumento,
        string $numeroDocumento,
        string $nombres,
        string $apellidos,
        string $email,
        string $celular
    ): array {
        try {
            $dto = new RegistroClienteDTO();
            $dto->setTipoDocumento(trim($tipoDocumento));
            $dto->setNumeroDocumento(trim($numeroDocumento));
            $dto->setNombres(trim($nombres));
            $dto->setApellidos(trim($apellidos));
            $dto->setEmail(trim($email));
            $dto->setCelular(trim($celular));

            $violations = $this->validator->validate($dto);

            if (count($violations) > 0) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CAMPOS_REQUERIDOS,
                    messageError: (string) $violations[0]->getMessage(),
                    data: null
                );
            }

            $cliente = new Cliente();
            $cliente->setTipoDocumento($dto->getTipoDocumento());
            $cliente->setNumeroDocumento($dto->getNumeroDocumento());
            $cliente->setNombres($dto->getNombres());
            $cliente->setApellidos($dto->getApellidos());
            $cliente->setEmail($dto->getEmail());
            $cliente->setCelular($dto->getCelular());
            $cliente->setFechaRegistro(new \DateTime());

            $this->entityManager->persist($cliente);
            $this->entityManager->flush();

            $billetera = new Billetera();
            $billetera->setCliente($cliente);
            $billetera->setSaldo('0.00');
            $billetera->setFechaCreacion(new \DateTime());

            $this->entityManager->persist($billetera);
            $this->entityManager->flush();

            $this->entityManager->refresh($cliente);

            return $this->generateStandardResponse(
                success: true,
                codError: ErrorCodes::SUCCESS,
                messageError: 'Cliente registrado exitosamente',
                data: [
                    'id' => $cliente->getId(),
                    'tipoDocumento' => $cliente->getTipoDocumento(),
                    'numeroDocumento' => $cliente->getNumeroDocumento(),
                    'nombres' => $cliente->getNombres(),
                    'apellidos' => $cliente->getApellidos(),
                    'email' => $cliente->getEmail(),
                    'celular' => $cliente->getCelular(),
                    'fechaRegistro' => $cliente->getFechaRegistro()->format('Y-m-d H:i:s'),
                    'billetera' => [
                        'id' => $cliente->getBilletera()->getId(),
                        'saldo' => $cliente->getBilletera()->getSaldo(),
                        'fechaCreacion' => $cliente->getBilletera()->getFechaCreacion()->format('Y-m-d H:i:s')
                    ]
                ]
            );
        } catch (\Doctrine\DBAL\Exception $e) {
            $message = $e->getMessage();
            
            if (stripos($message, 'email') !== false || stripos($message, 'UNIQ_50FE07D7E7927C74') !== false) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CLIENTE_DUPLICADO,
                    messageError: 'El correo electrónico ya está registrado en el sistema',
                    data: null
                );
            }
            
            if (stripos($message, 'numero_documento') !== false || stripos($message, 'UNIQ_4E0C8A11A2F35D4E') !== false) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CLIENTE_DUPLICADO,
                    messageError: 'El número de documento ya está registrado en el sistema',
                    data: null
                );
            }
            
            return $this->generateStandardResponse(
                success: false,
                codError: ErrorCodes::CLIENTE_DUPLICADO,
                messageError: 'Ya existe un registro con estos datos: ' . $message,
                data: null
            );
        } catch (\Exception $e) {
            return $this->generateStandardResponse(
                success: false,
                codError: ErrorCodes::ERROR_BD,
                messageError: 'Error al registrar el cliente: ' . $e->getMessage(),
                data: null
            );
        }
    }

    public function recargaBilletera(
        ?int $clienteId,
        string $documento,
        string $numeroCelular,
        float $valor
    ): array {
        try {
            $dto = new RecargaBilleteraDTO();
            $dto->setClienteId($clienteId);
            $dto->setDocumento(trim($documento));
            $dto->setNumeroCelular(trim($numeroCelular));
            $dto->setValor($valor);

            $violations = $this->validator->validate($dto);

            if (count($violations) > 0) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CAMPOS_REQUERIDOS,
                    messageError: (string) $violations[0]->getMessage(),
                    data: null
                );
            }

            $cliente = $this->clienteRepository->find($clienteId);

            if (!$cliente) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CLIENTE_NO_ENCONTRADO,
                    messageError: 'Cliente no encontrado',
                    data: null
                );
            }

            $billetera = $this->billeteraRepository->findByClienteId($cliente->getId());
            if (!$billetera) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CLIENTE_NO_ENCONTRADO,
                    messageError: 'Billetera no encontrada para el cliente',
                    data: null
                );
            }

            $this->entityManager->beginTransaction();

            $nuevoSaldo = (float)$billetera->getSaldo() + $dto->getValor();
            $billetera->setSaldo(number_format($nuevoSaldo, 2, '.', ''));

            $transaccion = new Transaccion();
            $transaccion->setBilletera($billetera);
            $transaccion->setTipo('recarga');
            $transaccion->setMonto(number_format($dto->getValor(), 2, '.', ''));
            $transaccion->setReferencia('RECARGA-' . uniqid());
            $transaccion->setEstado('completada');
            $transaccion->setFecha(new \DateTime());

            $this->entityManager->persist($billetera);
            $this->entityManager->persist($transaccion);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $this->generateStandardResponse(
                success: true,
                codError: ErrorCodes::SUCCESS,
                messageError: 'Recarga realizada exitosamente',
                data: [
                    'transaccionId' => $transaccion->getId(),
                    'nuevoSaldo' => $billetera->getSaldo(),
                    'valor' => $transaccion->getMonto(),
                    'fecha' => $transaccion->getFecha()->format('Y-m-d H:i:s')
                ]
            );
        } catch (\Doctrine\DBAL\Exception $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            return $this->generateStandardResponse(
                success: false,
                codError: ErrorCodes::ERROR_BD,
                messageError: 'Error al procesar recarga: ' . $e->getMessage(),
                data: null
            );
        } catch (\Exception $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            return $this->generateStandardResponse(
                success: false,
                codError: ErrorCodes::ERROR_BD,
                messageError: 'Error al procesar recarga: ' . $e->getMessage(),
                data: null
            );
        }
    }

    public function pagar(
        ?int $clienteId,
        float $monto,
        string $descripcion
    ): array {
        try {
            $dto = new PagarDTO();
            $dto->setClienteId($clienteId);
            $dto->setMonto($monto);
            $dto->setDescripcion(trim($descripcion));

            $violations = $this->validator->validate($dto);

            if (count($violations) > 0) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CAMPOS_REQUERIDOS,
                    messageError: (string) $violations[0]->getMessage(),
                    data: null
                );
            }

            $cliente = $this->clienteRepository->find($dto->getClienteId());

            if (!$cliente) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CLIENTE_NO_ENCONTRADO,
                    messageError: 'Cliente no encontrado',
                    data: null
                );
            }

            $billetera = $this->billeteraRepository->findByClienteId($cliente->getId());

            if (!$billetera) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::CLIENTE_NO_ENCONTRADO,
                    messageError: 'Billetera no encontrada para el cliente',
                    data: null
                );
            }

            if ((float)$billetera->getSaldo() < $dto->getMonto()) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::SALDO_INSUFICIENTE,
                    messageError: 'Saldo insuficiente',
                    data: null
                );
            }

            $sessionId = Uuid::v4()->toRfc4122();
            $token = (string)random_int(100000, 999999);
            $expirationTime = (new \DateTime())->modify('+15 minutes');

            $pagoPendiente = new PagoPendiente();
            $pagoPendiente->setBilletera($billetera);
            $pagoPendiente->setMonto(number_format($dto->getMonto(), 2, '.', ''));
            $pagoPendiente->setDescripcion($dto->getDescripcion());
            $pagoPendiente->setSessionId($sessionId);
            $pagoPendiente->setToken($token);
            $pagoPendiente->setEstado('pendiente');
            $pagoPendiente->setFechaCreacion(new \DateTime());
            $pagoPendiente->setFechaExpiracion($expirationTime);

            $this->entityManager->persist($pagoPendiente);
            $this->entityManager->flush();

            $email = (new Email())
                ->from('noreply@epayco.local')
                ->to($billetera->getCliente()->getEmail())
                ->subject('Token de Confirmación de Pago - ePayco Wallet')
                ->html(sprintf(
                    '<h2>Confirmación de Pago</h2>
                    <p>Token de confirmación: <strong style="font-size: 24px; color: #007bff;">%s</strong></p>
                    <p>Monto: <strong>$%s</strong></p>
                    <p>Descripción: %s</p>
                    <p>Este token expira en 15 minutos</p>
                    <p><em>No compartas este token con nadie</em></p>',
                    $token,
                    number_format($dto->getMonto(), 2),
                    htmlspecialchars($dto->getDescripcion())
                ));

            try {
                $this->mailer->send($email);
            } catch (\Exception $e) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: ErrorCodes::ERROR_EMAIL,
                    messageError: 'Error al enviar el email con el token: ' . $e->getMessage(),
                    data: null
                );
            }

            return $this->generateStandardResponse(
                success: true,
                codError: ErrorCodes::SUCCESS,
                messageError: 'Pago iniciado. Token enviado al email.',
                data: [
                    'sessionId' => $sessionId,
                    'monto' => $dto->getMonto(),
                    'tiempoExpiracion' => '15 minutos',
                    'mensajeEmail' => 'Se ha enviado un token de 6 dígitos a tu email'
                ]
            );
        } catch (\Doctrine\DBAL\Exception $e) {
            return $this->generateStandardResponse(
                success: false,
                codError: ErrorCodes::ERROR_BD,
                messageError: 'Error al procesar pago: ' . $e->getMessage(),
                data: null
            );
        } catch (\Exception $e) {
            return $this->generateStandardResponse(
                success: false,
                codError: ErrorCodes::ERROR_BD,
                messageError: 'Error al procesar pago: ' . $e->getMessage(),
                data: null
            );
        }
    }

      public function confirmarPago(
          string $sessionId,
          string $token
      ): array {
          try {
              $dto = new ConfirmarPagoDTO();
              $dto->setSessionId(trim($sessionId));
              $dto->setToken(trim($token));

              $violations = $this->validator->validate($dto);

              if (count($violations) > 0) {
                  return $this->generateStandardResponse(
                      success: false,
                      codError: ErrorCodes::CAMPOS_REQUERIDOS,
                      messageError: (string) $violations[0]->getMessage(),
                      data: null
                  );
              }

              $pagoPendiente = $this->pagoPendienteRepository->findBySessionId($dto->getSessionId());
              if (!$pagoPendiente) {
                  return $this->generateStandardResponse(
                      success: false,
                      codError: ErrorCodes::SESION_PAGO_NO_ENCONTRADA,
                      messageError: 'Sesión de pago no encontrada',
                      data: null
                  );
              }

              if ($pagoPendiente->getFechaExpiracion() < new \DateTime()) {
                  return $this->generateStandardResponse(
                      success: false,
                      codError: ErrorCodes::SESION_EXPIRADA,
                      messageError: 'Sesión expirada',
                      data: null
                  );
              }

              if ($pagoPendiente->getToken() !== $dto->getToken()) {
                  return $this->generateStandardResponse(
                      success: false,
                      codError: ErrorCodes::TOKEN_INCORRECTO,
                      messageError: 'Token incorrecto',
                      data: null
                  );
              }

              $this->entityManager->beginTransaction();

              $billetera = $pagoPendiente->getBilletera();
              $nuevoSaldo = (float)$billetera->getSaldo() - (float)$pagoPendiente->getMonto();
              $billetera->setSaldo(number_format($nuevoSaldo, 2, '.', ''));

              $transaccion = new Transaccion();
              $transaccion->setBilletera($billetera);
              $transaccion->setTipo('pago');
              $transaccion->setMonto(number_format($pagoPendiente->getMonto(), 2, '.', ''));
              $transaccion->setReferencia($dto->getSessionId());
              $transaccion->setEstado('completada');
              $transaccion->setFecha(new \DateTime());

              $pagoPendiente->setEstado('confirmado');
              $pagoPendiente->setFechaConfirmacion(new \DateTime());

              $this->entityManager->persist($billetera);
              $this->entityManager->persist($transaccion);
              $this->entityManager->persist($pagoPendiente);
              $this->entityManager->flush();
              $this->entityManager->commit();

              return $this->generateStandardResponse(
                  success: true,
                  codError: ErrorCodes::SUCCESS,
                  messageError: 'Pago confirmado exitosamente',
                  data: [
                      'transaccionId' => $transaccion->getId(),
                      'monto' => $transaccion->getMonto(),
                      'nuevoSaldo' => $billetera->getSaldo(),
                      'fecha' => $transaccion->getFecha()->format('Y-m-d H:i:s'),
                  ]
              );
          } catch (\Doctrine\DBAL\Exception $e) {
              if ($this->entityManager->getConnection()->isTransactionActive()) {
                  $this->entityManager->rollback();
              }
              return $this->generateStandardResponse(
                  success: false,
                  codError: ErrorCodes::ERROR_BD,
                  messageError: 'Error al confirmar pago: ' . $e->getMessage(),
                  data: null
              );
          } catch (\Exception $e) {
              if ($this->entityManager->getConnection()->isTransactionActive()) {
                  $this->entityManager->rollback();
              }
              return $this->generateStandardResponse(
                  success: false,
                  codError: ErrorCodes::ERROR_BD,
                  messageError: 'Error al confirmar pago: ' . $e->getMessage(),
                  data: null
              );
          }
      }

     public function consultarSaldo(int $clienteId): array
     {
         try {
             $billetera = $this->billeteraRepository->findByClienteId($clienteId);
             if (!$billetera) {
                 return $this->generateStandardResponse(
                     success: false,
                     codError: ErrorCodes::CLIENTE_NO_ENCONTRADO,
                     messageError: 'Billetera no encontrada',
                     data: null
                 );
             }

             $transacciones = $this->transaccionRepository->findByBilleteraId($billetera->getId());

             return $this->generateStandardResponse(
                 success: true,
                 codError: ErrorCodes::SUCCESS,
                 messageError: 'Consulta realizada exitosamente',
                 data: [
                     'saldo' => $billetera->getSaldo(),
                     'fechaUltimaActualizacion' => $billetera->getFechaActualizacion()?->format('Y-m-d H:i:s'),
                     'totalTransacciones' => count($transacciones),
                     'cliente' => [
                         'id' => $billetera->getCliente()->getId(),
                         'nombres' => $billetera->getCliente()->getNombres(),
                         'apellidos' => $billetera->getCliente()->getApellidos(),
                         'email' => $billetera->getCliente()->getEmail(),
                     ],
                 ]
             );
         } catch (\Exception $e) {
             return $this->generateStandardResponse(
                 success: false,
                 codError: ErrorCodes::ERROR_BD,
                 messageError: 'Error al consultar saldo: ' . $e->getMessage(),
                 data: null
             );
         }
     }

        private function generateStandardResponse(bool $success, string $codError, string $messageError, $data = null): array
        {
            return [
                'success' => $success,
                'cod_error' => $codError,
                'message_error' => $messageError,
                'data' => $data ?? []
            ];
        }
}
