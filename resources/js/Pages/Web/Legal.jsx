import { Head } from '@inertiajs/react';
import PublicLayout from './PublicLayout';

export default function Legal({ title, content }) {
    return (
        <PublicLayout>
            <Head title={title} />
            <main className="bg-[#f5f8fc] px-5 py-16 sm:px-6 lg:py-24">
                <article className="prose prose-slate mx-auto max-w-4xl rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-12">
                    <h1>{title}</h1>
                    {content ? (
                        <div dangerouslySetInnerHTML={{ __html: content }} />
                    ) : (
                        <p>Ce document sera prochainement disponible.</p>
                    )}
                </article>
            </main>
        </PublicLayout>
    );
}
