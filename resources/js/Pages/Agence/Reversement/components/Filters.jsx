import React from 'react';

export default function Filters({
    proprietaires,
    filterProprietaire,
    onFilterPropChange,
    resultCount
}) {
    return (
        <>
            <div className="filters-grid">
                <div className="field">
                    <label>Propriétaire</label>
                    <select 
                        value={filterProprietaire}
                        onChange={(e) => onFilterPropChange(e.target.value)}
                    >
                        <option value="all">Tous les propriétaires</option>
                        {proprietaires.map((p) => (
                            <option key={p.id} value={p.id}>
                                {p.nom}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="field">
                    <label>Date début</label>
                    <input type="date" defaultValue="2026-07-01" />
                </div>
                <div className="field">
                    <label>Date fin</label>
                    <input type="date" defaultValue="2026-07-31" />
                </div>
                <div className="field">
                    <button className="btn btn-primary">Filtrer</button>
                </div>
            </div>
            <div className="period-banner">
                ⓘ Période : 01/07/2026 au 31/07/2026 · {resultCount} résultat(s) trouvé(s)
            </div>
        </>
    );
}