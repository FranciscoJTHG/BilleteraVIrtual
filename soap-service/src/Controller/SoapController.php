<?php

namespace App\Controller;

use App\Service\WalletService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SoapController extends AbstractController
{
    public function __construct(
        private WalletService $walletService
    ) {}

    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function health(): Response
    {
        return new Response(json_encode(['status' => 'ok']), 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/soap', name: 'soap_server', methods: ['POST', 'GET'])]
    public function server(Request $request): Response
    {
        $server = new \SoapServer(__DIR__ . '/../../public/wallet.wsdl');
        $server->setObject($this->walletService);

        ob_start();
        $server->handle();
        $response = ob_get_clean();

        return new Response($response, 200, ['Content-Type' => 'text/xml']);
    }

    #[Route('/soap/wsdl', name: 'soap_wsdl', methods: ['GET'])]
    public function wsdl(): Response
    {
        $wsdlPath = __DIR__ . '/../../public/wallet.wsdl';

        if (!file_exists($wsdlPath)) {
            throw $this->createNotFoundException('WSDL file not found');
        }

        $wsdl = file_get_contents($wsdlPath);
        return new Response($wsdl, 200, ['Content-Type' => 'text/xml']);
    }
}
