import { Head } from '@inertiajs/react';
import RoleEditor from './RoleEditor';

export default function RolePermissionsPage(props) {
    return (
        <>
            <Head title="Configurer les permissions" />
            <RoleEditor {...props} mode="edit" />
        </>
    );
}
