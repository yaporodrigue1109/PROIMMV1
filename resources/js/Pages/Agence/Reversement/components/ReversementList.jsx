import React from 'react';

export default function ReversementList({
    cours,
    filterSearch,
    onSearchChange,
    onOpenFiche,
    fmt,
    propNom,
    propTel,
    courTotals
}) {
    return (
        <>
            <div className="table-toolbar">
                <select style={{ width: '80px' }}>
                    <option>25</option>
                </select>
                <input
                    type="text"
                    placeholder="Rechercher..."
                    value={filterSearch}
                    onChange={(e) => onSearchChange(e.target.value)}
                />
            </div>
            <div className="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Nom de la cour</th>
                            <th>Propriétaire</th>
                            <th>Montant attendu</th>
                            <th>Montant payé</th>
                            <th>Montant restant</th>
                            <th>Statut</th>
                            <th>Fiche</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cours.length > 0 ? (
                            cours.map((c) => {
                                const t = courTotals(c);
                                return (
                                    <tr key={c.id}>
                                        <td className="cell-cour">
                                            {c.nom}
                                            <small>{c.locataires.length} locataire(s)</small>
                                        </td>
                                        <td>
                                            {propNom(c.proprietaireId)}
                                            <small>{propTel(c.proprietaireId)}</small>
                                        </td>
                                        <td className="amount-blue">{fmt(t.attendu)}</td>
                                        <td>
                                            <span className="amount-green">{fmt(t.totalPaye)}</span>
                                            <div className="progress-wrap">
                                                <div className="progress-bar" style={{ width: `${t.pct}%` }}></div>
                                            </div>
                                            <span className="pct">{t.pct}% payé</span>
                                        </td>
                                        <td className={t.restant > 0 ? 'amount-red' : 'amount-green'}>
                                            {fmt(t.restant)}
                                        </td>
                                        <td>
                                            <span className={`badge ${c.statut === 'reverse' ? 'reverse' : 'attente'}`}>
                                                {c.statut === 'reverse' ? 'Reversé' : 'En attente'}
                                            </span>
                                        </td>
                                        <td>
                                            <button 
                                                className="icon-btn" 
                                                title="Voir la fiche" 
                                                onClick={() => onOpenFiche(c.id)}
                                            >
                                                📄
                                            </button>
                                        </td>
                                    </tr>
                                );
                            })
                        ) : (
                            <tr>
                                <td colSpan="7" style={{ textAlign: 'center', color: 'var(--muted)', padding: '24px' }}>
                                    Aucun résultat pour ces filtres.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </>
    );
}