import React from 'react';
import Stats from './Stats';
import Filters from './Filters';
import ReversementList from './ReversementList';

export default function Dashboard({
    cours,
    proprietaires,
    filterProprietaire,
    filterSearch,
    totals,
    onFilterPropChange,
    onSearchChange,
    onOpenFiche,
    fmt,
    propNom,
    propTel,
    courTotals
}) {
    return (
        <>
            <Stats 
                totalAttendu={totals.totalAttendu}
                totalPaye={totals.totalPaye}
                totalRestant={totals.totalRestant}
                fmt={fmt}
            />

            <div className="card">
                <div className="card-head">Filtres</div>
                <div className="card-body">
                    <Filters
                        proprietaires={proprietaires}
                        filterProprietaire={filterProprietaire}
                        onFilterPropChange={onFilterPropChange}
                        resultCount={cours.length}
                    />
                </div>
            </div>

            <div className="card">
                <div className="card-head">Liste des biens</div>
                <ReversementList
                    cours={cours}
                    filterSearch={filterSearch}
                    onSearchChange={onSearchChange}
                    onOpenFiche={onOpenFiche}
                    fmt={fmt}
                    propNom={propNom}
                    propTel={propTel}
                    courTotals={courTotals}
                />
            </div>
        </>
    );
}