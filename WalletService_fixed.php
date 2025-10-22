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
            $cliente = new Cliente();
            $cliente->setTipoDocumento(trim($tipoDocumento));
            $cliente->setNumeroDocumento(trim($numeroDocumento));
            $cliente->setNombres(trim($nombres));
            $cliente->setApellidos(trim($apellidos));
            $cliente->setEmail(trim($email));
            $cliente->setCelular(trim($celular));
            $cliente->setFechaRegistro(new \DateTime());

            $violations = $this->validator->validate($cliente);

            if (count($violations) > 0) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: 'CAMPO_REQUERIDO',
                    messageError: (string) $violations[0]->getMessage(),
                    data: null
                );
            }

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
                codError: '',
                messageError: '',
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
                    codError: 'EMAIL_DUPLICADO',
                    messageError: 'El correo electrónico ya está registrado en el sistema',
                    data: null
                );
            }
            
            if (stripos($message, 'numero_documento') !== false || stripos($message, 'UNIQ_4E0C8A11A2F35D4E') !== false) {
                return $this->generateStandardResponse(
                    success: false,
                    codError: 'DOCUMENTO_DUPLICADO',
                    messageError: 'El número de documento ya está registrado en el sistema',
                    data: null
                );
            }
            
            return $this->generateStandardResponse(
                success: false,
                codError: 'RESTRICCION_UNICA_VIOLADA',
                messageError: 'Ya existe un registro con estos datos: ' . $message,
                data: null
            );
        } catch (\Exception $e) {
            return $this->generateStandardResponse(
                success: false,
                codError: 'ERROR_REGISTRO',
                messageError: 'Error al registrar el cliente: ' . $e->getMessage(),
                data: null
            );
        }
    }

    private function validarCamposRegistro(
        string $tipoDocumento,
        string $numeroDocumento,
        string $nombres,
        string $apellidos,
        string $email,
        string $celular
    ): array {
        $errores = [];

        if (empty(trim($tipoDocumento))) {
            $errores[] = 'El tipo de documento es requerido';
        }

        if (empty(trim($numeroDocumento))) {
            $errores[] = 'El número de documento es requerido';
        }

        if (empty(trim($nombres))) {
            $errores[] = 'El nombre es requerido';
        }

        if (empty(trim($apellidos))) {
            $errores[] = 'El apellido es requerido';
        }

        if (empty(trim($email))) {
            $errores[] = 'El correo electrónico es requerido';
        } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido';
        }

        if (empty(trim($celular))) {
            $errores[] = 'El celular es requerido';
        } elseif (!preg_match('/^\d{10}$/', trim($celular))) {
            $errores[] = 'El celular debe contener exactamente 10 dígitos';
        }

        return $errores;
    }

    public function recargaBilletera(
        int $clienteId,
        float $monto,
        string $referencia
    ): Transaccion {
        $billetera = $this->billeteraRepository->findByClienteId($clienteId);
        if (!$billetera) {
            throw new \RuntimeException("Billetera no encontrada para el cliente");
        }

        $this->entityManager->beginTransaction();

        try {
            $nuevoSaldo = (float)$billetera->getSaldo() + $monto;
            $billetera->setSaldo(number_format($nuevoSaldo, 2, '.', ''));

            $transaccion = new Transaccion();
            $transaccion->setBilletera($billetera);
            $transaccion->setTipo('recarga');
            $transaccion->setMonto(number_format($monto, 2, '.', ''));
            $transaccion->setReferencia($referencia);
            $transaccion->setEstado('completada');
            $transaccion->setFecha(new \DateTime());

            $this->entityManager->persist($billetera);
            $this->entityManager->persist($transaccion);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $transaccion;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \RuntimeException("Error al procesar recarga: " . $e->getMessage());
        }
    }

    public function pagar(
        int $clienteId,
        float $monto,
        string $descripcion
    ): array {
        $billetera = $this->billeteraRepository->findByClienteId($clienteId);
        if (!$billetera) {
            throw new \RuntimeException("Billetera no encontrada");
        }

        if ($billetera->getSaldo() < $monto) {
            throw new \RuntimeException("Saldo insuficiente");
        }

        $token = Uuid::v4()->toRfc4122();
        $expirationTime = (new \DateTime())->modify('+15 minutes');

        $pagoPendiente = new PagoPendiente();
        $pagoPendiente->setBilletera($billetera);
        $pagoPendiente->setMonto($monto);
        $pagoPendiente->setDescripcion($descripcion);
        $pagoPendiente->setSessionId($token);
        $pagoPendiente->setEstado('pendiente');
        $pagoPendiente->setFechaCreacion(new \DateTime());
        $pagoPendiente->setFechaExpiracion($expirationTime);

        $this->entityManager->persist($pagoPendiente);
        $this->entityManager->flush();

        $email = (new Email())
            ->from('noreply@epayco.local')
            ->to($billetera->getCliente()->getEmail())
            ->subject('Confirmación de Pago - ePayco Wallet')
            ->html(sprintf(
                '<h2>Confirmación de Pago</h2>
                <p>Token de confirmación: <strong>%s</strong></p>
                <p>Monto: $%s</p>
                <p>Descripción: %s</p>
                <p>Este token expira en 15 minutos</p>',
                $token,
                number_format($monto, 2),
                htmlspecialchars($descripcion)
            ));

        $this->mailer->send($email);

        return [
            'sessionId' => $token,
            'monto' => $monto,
            'expiresAt' => $expirationTime->format('Y-m-d H:i:s'),
        ];
    }

    public function confirmarPago(
        string $sessionId,
        string $token
    ): array {
        $pagoPendiente = $this->pagoPendienteRepository->findBySessionId($sessionId);
        if (!$pagoPendiente) {
            throw new \RuntimeException("Sesión de pago no encontrada");
        }

        if ($pagoPendiente->getFechaExpiracion() < new \DateTime()) {
            throw new \RuntimeException("Token expirado");
        }

        if ($pagoPendiente->getToken() !== $token) {
            throw new \RuntimeException("Token inválido");
        }

        $this->entityManager->beginTransaction();

        try {
            $billetera = $pagoPendiente->getBilletera();
            $nuevoSaldo = $billetera->getSaldo() - $pagoPendiente->getMonto();
            $billetera->setSaldo($nuevoSaldo);

            $transaccion = new Transaccion();
            $transaccion->setBilletera($billetera);
            $transaccion->setTipo('pago');
            $transaccion->setMonto($pagoPendiente->getMonto());
            $transaccion->setReferencia($sessionId);
            $transaccion->setEstado('completada');
            $transaccion->setFecha(new \DateTime());

            $pagoPendiente->setEstado('confirmado');
            $pagoPendiente->setFechaConfirmacion(new \DateTime());

            $this->entityManager->persist($billetera);
            $this->entityManager->persist($transaccion);
            $this->entityManager->persist($pagoPendiente);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return [
                'status' => 'confirmado',
                'monto' => $pagoPendiente->getMonto(),
                'nuevoSaldo' => $nuevoSaldo,
                'fecha' => $transaccion->getFecha()->format('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \RuntimeException("Error al confirmar pago: " . $e->getMessage());
        }
    }

      public function consultarSaldo(int $clienteId): array
      {
          $billetera = $this->billeteraRepository->findByClienteId($clienteId);
          if (!$billetera) {
              throw new \RuntimeException("Billetera no encontrada");
          }

          $transacciones = $this->transaccionRepository->findByBilleteraId($billetera->getId());

          return [
              'saldo' => $billetera->getSaldo(),
              'fechaUltimaActualizacion' => $billetera->getFechaActualizacion()?->format('Y-m-d H:i:s'),
              'totalTransacciones' => count($transacciones),
              'cliente' => [
                  'id' => $billetera->getCliente()->getId(),
                  'nombres' => $billetera->getCliente()->getNombres(),
                  'apellidos' => $billetera->getCliente()->getApellidos(),
                  'email' => $billetera->getCliente()->getEmail(),
              ],
          ];
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
