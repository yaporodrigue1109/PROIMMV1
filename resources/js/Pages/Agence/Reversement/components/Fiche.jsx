import React, { useState, useEffect } from 'react';
import FicheDetails from './FicheDetails';

export default function Fiche({
    cour,
    proprietaires,
    onBack,
    onUpdateLocataire,
    onUpdateCour,
    fmt,
    fmtDate,
    today,
    courTotals,
    propNom
}) {
    const t = courTotals(cour);
    const readonly = cour.statut === 'reverse';
    const [forceUpdate, setForceUpdate] = useState(0);

    const handleLocataireChange = (idx, field, value) => {
        onUpdateLocataire(cour.id, idx, field, value);
        setForceUpdate(prev => prev + 1);
    };

    const handleCourFieldChange = (field, value) => {
        onUpdateCour(cour.id, { [field]: field === 'observation' ? value : Number(value) || 0 });
        setForceUpdate(prev => prev + 1);
    };

    const handleMarquerReverse = () => {
        onUpdateCour(cour.id, { statut: 'reverse' });
    };

    const handleRouvrirFiche = () => {
        onUpdateCour(cour.id, { statut: 'en_attente' });
    };

    const handlePrint = () => {
        window.print();
    };

    return (
        <>
            <button className="back-link no-print" onClick={onBack}>
                ← Retour au tableau de bord
            </button>

            <div className="card">
                <div className="fiche-head">
                    <div className="fiche-title">
                        <h2>Fiche d'encaissement de loyers</h2>
                        <p>
                            Nom du bailleur : <b>{propNom(cour.proprietaireId)}</b>
                        </p>
                        <p>
                            Cours : <b>{cour.nom}</b>
                        </p>
                    </div>
                    <div className="fiche-period">
                        Période
                        <br />
                        <b>
                            {fmtDate(cour.periode.debut)} - {fmtDate(cour.periode.fin)}
                        </b>
                    </div>
                </div>

                <div className={`status-banner ${cour.statut === 'reverse' ? 'reverse' : 'attente'}`}>
                    {cour.statut === 'reverse'
                        ? '✓ Ce reversement a déjà été effectué au bailleur.'
                        : '⚠ Reversement en attente — complétez les paiements puis validez.'}
                </div>

                <div className="fiche-actions no-print">
                    <button className="btn btn-outline" onClick={handlePrint}>
                        🖨️ Imprimer / Exporter
                    </button>
                    {!readonly ? (
                        <button className="btn btn-green" onClick={handleMarquerReverse}>
                            ✓ Marquer comme reversé
                        </button>
                    ) : (
                        <button className="btn btn-outline" onClick={handleRouvrirFiche}>
                            ↺ Rouvrir la fiche
                        </button>
                    )}
                </div>

                <FicheDetails
                    cour={cour}
                    t={t}
                    readonly={readonly}
                    fmt={fmt}
                    onLocataireChange={handleLocataireChange}
                    onCourFieldChange={handleCourFieldChange}
                    today={today}
                />
            </div>
        </>
    );
}