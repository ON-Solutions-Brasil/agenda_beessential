<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\RoomReservation;
use App\Models\Unit;

/**
 * Histórico de clientes atendidos (registro simples para consulta).
 */
class ClientController extends Controller
{
    /**
     * Lista o histórico de clientes/atendimentos.
     */
    public function index(): void
    {
        $this->requireSuperAdmin();

        $search = trim((string) $this->query('q', ''));
        $unitId = (int) $this->query('unit', 0);
        $selectedUnit = $unitId > 0 ? $unitId : null;

        $reservationModel = new RoomReservation();
        $unitModel = new Unit();

        $this->view('clients/index', [
            'clients'      => $reservationModel->getClientHistory($search, 300, $selectedUnit),
            'search'       => $search,
            'units'        => $unitModel->allOrdered(),
            'selectedUnit' => $selectedUnit,
        ]);
    }
}
