import { Head } from '@inertiajs/react';
import RoleEditor from './RoleEditor';

export default function CreateRolePage(props) {
    return (
        <>
            <Head title="Créer un rôle" />
            <RoleEditor {...props} mode="create" />
        </>
    );
}
