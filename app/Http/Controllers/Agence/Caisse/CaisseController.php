<?php

namespace App\Http\Controllers\Agence\Caisse;

use App\Http\Controllers\Controller;
use App\Models\ModePaiement;
use App\Repositories\Agence\Interfaces\TransactionAgenceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class CaisseController extends Controller
{
    protected $transactionRepository;

    public function __construct(TransactionAgenceRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function index()
    {
        $agenceId = $this->agenceId();
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        $transactions = $this->transactionRepository->getByAgence($agenceId)
            ->filter(function ($transaction) use ($startOfDay, $endOfDay) {
                if (empty($transaction->date_transaction)) {
                    return false;
                }

                $transactionDate = $transaction->date_transaction instanceof Carbon
                    ? $transaction->date_transaction
                    : Carbon::parse($transaction->date_transaction);

                return $transactionDate->between($startOfDay, $endOfDay, true);
            });

        $stats = [
            'total_transactions' => $transactions->count(),
            'total_versements' => (float) $transactions->sum('montant_global_verser'),
            'total_loyers_payes' => (float) $transactions->where('type_transaction', 'loyer')->sum('montant_loyer_payer'),
            'total_arrieres' => (float) $transactions->sum('arriere_actuel'),
            'total_arrieres_payes' => (float) $transactions->sum('montant_arriere_payer'),
            'total_avances' => (float) $transactions->sum('montant_avance_payer'),
            'total_reversements' => $transactions->where('is_reversement', 1)->count(),
        ];

        // Calcul des totaux pour le composant React
        $totalEntrees = $transactions->whereIn('type_transaction', ['loyer', 'vente'])->sum('montant_global_verser');
        $totalSorties = $transactions->whereIn('type_transaction', ['maintenance', 'depense'])->sum('montant_global_verser');

        // Préparer les transactions pour le composant React
        $transactionsData = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'type' => in_array($transaction->type_transaction, ['loyer', 'vente']) ? 'in' : 'out',
                'label' => $this->getTransactionLabel($transaction),
                'reference' => $transaction->transaction_agence_id,
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->toArray();

        // Préparer les loyers
        $loyersData = $transactions->where('type_transaction', 'loyer')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'tenant' => $transaction->locataire->name ?? 'N/A',
                'property' => $this->getPropertyName($transaction),
                'period' => $transaction->mois_payer ?? 'N/A',
                'amount' => (float) $transaction->montant_global_verser,
                'mode' => $transaction->modePaiement->name ?? 'N/A',
            ];
        })->toArray();

        // Préparer les maintenances
        $maintenancesData = $transactions->where('type_transaction', 'maintenance')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'property' => $this->getPropertyName($transaction),
                'type' => 'Maintenance',
                'provider' => 'N/A', // À adapter selon votre structure
                'cost' => (float) $transaction->montant_global_verser,
                'status' => 'Terminée', // À adapter selon votre structure
            ];
        })->toArray();

        // Préparer les dépenses
        $depensesData = $transactions->where('type_transaction', 'depense')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'category' => 'Divers',
                'label' => $this->getTransactionLabel($transaction),
                'proof' => 'Reçu',
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->toArray();

        // Préparer les ventes
        $ventesData = $transactions->where('type_transaction', 'vente')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'client' => 'Client N/A',
                'property' => $transaction->propriete_id ?? 'N/A',
                'reference' => $transaction->transaction_agence_id,
                'amount' => (float) $transaction->montant_global_verser,
                'status' => 'Payée',
            ];
        })->toArray();

        // Préparer le résumé par mode de paiement
        $modesPaiement = ModePaiement::all();
           
        $summaryData = [];
        foreach ($modesPaiement as $mode) {
            $modeTransactions = $transactions->where('mode_paiement_id', $mode->mode_paiement_id);
            $totalMontant = $modeTransactions->sum('montant_global_verser');
            $nbTransactions = $modeTransactions->count();
            
            if ($nbTransactions > 0) {
                $commission = $totalMontant * 0.10;
                $net = $totalMontant - $commission;
                
                $summaryData[] = [
                    'mode' => $mode->name,
                    'total' => (float) $totalMontant,
                    'count' => $nbTransactions,
                    'commission' => (float) $commission,
                    'net' => (float) $net,
                    'icon' => $this->getModeIcon($mode->name),
                    'accent' => $this->getModeAccent($mode->name),
                ];
            }
        }

        // Si aucun résumé n'est disponible, ajouter des données par défaut
        if (empty($summaryData)) {
            $summaryData = [
                [
                    'mode' => 'Espèces',
                    'total' => 0,
                    'count' => 0,
                    'commission' => 0,
                    'net' => 0,
                    'icon' => 'Wallet',
                    'accent' => 'bg-[#eaf4fb] text-[#00559b]',
                ],
                [
                    'mode' => 'WAVE',
                    'total' => 0,
                    'count' => 0,
                    'commission' => 0,
                    'net' => 0,
                    'icon' => 'Smartphone',
                    'accent' => 'bg-[#00559b]/10 text-[#00559b]',
                ],
                [
                    'mode' => 'Orange Money',
                    'total' => 0,
                    'count' => 0,
                    'commission' => 0,
                    'net' => 0,
                    'icon' => 'CreditCard',
                    'accent' => 'bg-[#fff2e6] text-[#c2410c]',
                ],
            ];
        }

        return Inertia::render('Agence/Caisse/Index', [
            'caisseOuverte' => true,
            'soldeOuverture' => 125000, // À récupérer d'une table de caisse
            'totalEntrees' => (float) $totalEntrees,
            'totalSorties' => (float) $totalSorties,
            'transactions' => $transactionsData,
            'loyers' => $loyersData,
            'maintenance' => $maintenancesData,
            'depenses' => $depensesData,
            'ventes' => $ventesData,
            'summary' => $summaryData,
            'statistiques' => $stats,
        ]);
    }

    public function maintenance()
    {
        $agenceId = $this->agenceId();

        // Récupérer les maintenances depuis la table maintenance
        $maintenancesData = $this->safeMaintenanceRows($agenceId);
        
        // Récupérer les transactions de maintenance pour les stats
        $transactions = $this->transactionRepository->getByAgence($agenceId);
        $totalMaintenance = $transactions->where('type_transaction', 'maintenance')->sum('montant_global_verser');

        return Inertia::render('Agence/Caisse/Maintenance', [
            'caisseOuverte' => true,
            'maintenances' => $maintenancesData,
            'totalMaintenance' => (float) $totalMaintenance,
            'proprietaires' => $this->safeTableRows('proprietaires', ['proprietaire_id', 'name']),
            'lots' => $this->safeTableRows('propietaire_lots', ['propreietaire_lot_id', 'name', 'proprietaire_id']),
            'batiments' => $this->safeTableRows('batiment', ['batiment_id', 'name', 'propriete_id']),
            'portes' => $this->safeTableRows('porte', ['porte_id', 'numero_porte', 'batiment_id']),
            'typesIntervention' => $this->safeTableRows('type_maintenances', ['type_maintenance_id', 'name', 'description']),
            'maintenanciers' => $this->safeTableRows('maintenanciers', ['maintenancier_id', 'name', 'fonction_maintenance_id']),
        ]);
    }

    public function loyer()
    {
        $agenceId = $this->agenceId();
        $transactions = $this->transactionRepository->getByAgence($agenceId);
        
        $loyersData = $transactions->where('type_transaction', 'loyer')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'tenant' => $transaction->locataire->name ?? 'N/A',
                'property' => $this->getPropertyName($transaction),
                'period' => $transaction->mois_payer ?? 'N/A',
                'amount' => (float) $transaction->montant_global_verser,
                'mode' => $transaction->modePaiement->name ?? 'N/A',
            ];
        })->toArray();

        $totalLoyers = $transactions->where('type_transaction', 'loyer')->sum('montant_global_verser');

        return Inertia::render('Agence/Caisse/Loyer', [
            'caisseOuverte' => true,
            'loyers' => $loyersData,
            'totalLoyers' => (float) $totalLoyers,
        ]);
    }

    public function depenseAgence()
    {
        $agenceId = $this->agenceId();
        $transactions = $this->transactionRepository->getByAgence($agenceId);
        
        $depensesData = $transactions->where('type_transaction', 'depense')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'category' => $transaction->modePaiement->name ?? 'Divers',
                'label' => $this->getTransactionLabel($transaction),
                'proof' => 'Reçu',
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->toArray();

        $totalDepenses = $transactions->where('type_transaction', 'depense')->sum('montant_global_verser');

        return Inertia::render('Agence/Caisse/DepenseAgence', [
            'caisseOuverte' => true,
            'depenses' => $depensesData,
            'totalDepenses' => (float) $totalDepenses,
        ]);
    }

    public function venteBien()
    {
        $agenceId = $this->agenceId();
        $transactions = $this->transactionRepository->getByAgence($agenceId);

        $ventesData = $transactions->where('type_transaction', 'vente')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'client' => 'Client N/A',
                'property' => $transaction->propriete_id ?? 'N/A',
                'reference' => $transaction->transaction_agence_id,
                'amount' => (float) $transaction->montant_global_verser,
                'status' => 'Payée',
            ];
        })->toArray();

        $totalVentes = $transactions->where('type_transaction', 'vente')->sum('montant_global_verser');

        $ownerLinks = \App\Models\ProprietaireAgence::query()
            ->where('agence_id', $agenceId)
            ->where('is_active', true)
            ->with(['proprietaire' => function ($query) use ($agenceId) {
                $query->with(['proprietes' => function ($propertyQuery) use ($agenceId) {
                    $propertyQuery->where('agence_id', $agenceId)
                        ->where('is_actif', true)
                        ->with(['batiments' => function ($batimentQuery) {
                            $batimentQuery->with(['portes' => function ($porteQuery) {
                                $porteQuery->where('is_actif', true)
                                    ->with('tarifActif');
                            }]);
                        }]);
                }]);
            }])
            ->get();

        $saleOwners = $ownerLinks->map(function ($link) {
            $owner = $link->proprietaire;
            if (!$owner) {
                return null;
            }

            $properties = $owner->proprietes->filter(function ($property) {
                return $property->is_allocation === false;
            })->values()->map(function ($property) {
                $buildings = $property->batiments->map(function ($building) {
                    $doors = $building->portes->filter(function ($door) {
                        return $door->is_actif && $door->is_occupe === false && $door->is_allocation === false;
                    })->values()->map(function ($door) {
                        $tarif = $door->tarifActif;
                        return [
                            'id' => $door->porte_id,
                            'title' => $door->numero_porte ?? 'Porte',
                            'forSale' => true,
                            'price' => (float) ($tarif->mt_vente ?? 0),
                        ];
                    });

                    return [
                        'id' => $building->batiment_id,
                        'title' => $building->name ?? 'Bâtiment',
                        'forSale' => $doors->isNotEmpty(),
                        'doors' => $doors->values()->all(),
                    ];
                })->values();

                $propertyForSale = $property->is_allocation === false && $property->batiments->contains(function ($building) {
                    return $building->portes->contains(function ($door) {
                        return $door->is_actif && $door->is_occupe === false && $door->is_allocation === false;
                    });
                });

                return [
                    'id' => $property->propriete_id,
                    'title' => $property->reference ?? 'Bien immobilier',
                    'location' => $property->adresse_complete ?? '',
                    'type' => $property->typePropriete->name ?? 'Bien',
                    'price' => 0,
                    'commission' => 0,
                    'ownerAmount' => 0,
                    'buyer' => '',
                    'status' => 'Disponible',
                    'badge' => 'Disponible',
                    'reference' => $property->reference ?? '',
                    'date' => '',
                    'observation' => 'Disponible à la vente',
                    'forSale' => $propertyForSale,
                    'buildings' => $buildings->values()->all(),
                ];
            })->filter(function ($property) {
                return $property['forSale'] || collect($property['buildings'])->contains(fn ($building) => $building['forSale']);
            })->values();

            return [
                'id' => $owner->proprietaire_id,
                'name' => $owner->name ?? 'Propriétaire',
                'phone' => $owner->tel1 ?? '',
                'properties' => $properties->all(),
            ];
        })->filter(function ($owner) {
            return $owner !== null && !empty($owner['properties']);
        })->values();
        return Inertia::render('Agence/Caisse/VenteBien', [
            'caisseOuverte' => true,
            'ventes' => $ventesData,
            'totalVentes' => (float) $totalVentes,
            'saleOwners' => $saleOwners->all(),
        ]);
    }

    // Méthodes privées utilitaires

    private function getTransactionLabel($transaction)
    {
        switch ($transaction->type_transaction) {
            case 'loyer':
                $locataire = $transaction->locataire->name ?? 'N/A';
                $porte = $transaction->porte->numero_porte ?? 'N/A';
                $mois = $transaction->mois_payer ?? 'N/A';
                return "Paiement loyer — {$locataire} ({$porte}, {$mois})";
            case 'maintenance':
                $porte = $transaction->porte->numero_porte ?? 'N/A';
                return "Maintenance — {$porte}";
            case 'depense':
                $porte = $transaction->porte->numero_porte ?? 'N/A';
                return "Dépense agence — {$porte}";
            case 'vente':
                $proprieteId = $transaction->propriete_id ?? 'N/A';
                return "Vente de bien — {$proprieteId}";
            default:
                return "Transaction";
        }
    }

    private function getPropertyName($transaction)
    {
        if ($transaction->porte && $transaction->porte->numero_porte) {
            return $transaction->porte->numero_porte;
        }
        
        if ($transaction->batiment_id) {
            $batiment = DB::table('batiment')->where('batiment_id', $transaction->batiment_id)->first();
            return $batiment->name ?? 'N/A';
        }
        
        return 'N/A';
    }

    private function getModeIcon($modeName)
    {
        $icons = [
            'Espèces' => 'Wallet',
            'WAVE' => 'Smartphone',
            'Orange Money' => 'CreditCard',
            'Mobile Money' => 'Smartphone',
            'Virement' => 'Banknote',
        ];
        
        return $icons[$modeName] ?? 'Wallet';
    }

    private function getModeAccent($modeName)
    {
        $accents = [
            'Espèces' => 'bg-[#eaf4fb] text-[#00559b]',
            'WAVE' => 'bg-[#00559b]/10 text-[#00559b]',
            'Orange Money' => 'bg-[#fff2e6] text-[#c2410c]',
            'Mobile Money' => 'bg-[#eaf4fb] text-[#00559b]',
            'Virement' => 'bg-[#eef8df] text-[#4d8500]',
        ];
        
        return $accents[$modeName] ?? 'bg-[#f1f5f9] text-[#5f7182]';
    }

    private function safeMaintenanceRows(string $agenceId): array
    {
        if (! Schema::hasTable('maintenance')) {
            return [];
        }

        return DB::table('maintenance')
            ->where('agence_id', $agenceId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get([
                'maintenance_id',
                'titre',
                'description',
                'statut',
                'montant_global',
                'proprietaire_id',
                'lot_id',
                'batiment_id',
                'porte_id',
                'prise_en_charge_par',
                'created_at',
            ])
            ->map(function ($row) {
                return [
                    'id' => $row->maintenance_id,
                    'date' => $row->created_at ? date('d/m/Y', strtotime($row->created_at)) : '',
                    'time' => $row->created_at ? date('H:i', strtotime($row->created_at)) : '',
                    'property' => $row->batiment_id ?? 'N/A',
                    'type' => $row->titre ?? 'Maintenance',
                    'provider' => $row->prise_en_charge_par ?? 'N/A',
                    'cost' => (float) ($row->montant_global ?? 0),
                    'status' => $row->statut ?? 'En cours',
                    'description' => $row->description ?? '',
                    'proprietaire_id' => $row->proprietaire_id,
                    'lot_id' => $row->lot_id,
                    'batiment_id' => $row->batiment_id,
                    'porte_id' => $row->porte_id,
                ];
            })
            ->toArray();
    }

    private function safeTableRows(string $table, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->orderBy($columns[1] ?? $columns[0])
            ->get($columns)
            ->map(function ($row) use ($columns) {
                // Transformer en tableau associatif avec des clés value/label pour les selects
                $result = [];
                $firstKey = $columns[0];
                $secondKey = $columns[1] ?? $columns[0];
                
                $result['value'] = $row->$firstKey;
                $result['label'] = $row->$secondKey;
                
                // Ajouter les autres champs
                foreach ($columns as $key) {
                    if ($key !== $firstKey && $key !== $secondKey) {
                        $result[$key] = $row->$key;
                    }
                }
                
                return $result;
            })
            ->toArray();
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
}