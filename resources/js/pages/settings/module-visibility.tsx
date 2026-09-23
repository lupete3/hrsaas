import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { PageTemplate } from '@/components/page-template';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Input } from '@/components/ui/input';

export default function ModuleVisibility({ modules, visibility }: {
    modules: Record<string, { label: string; group: string }>;
    visibility: Record<string, boolean>;
}) {
    const { t } = useTranslation();
    const { data, setData, put, processing, errors, isDirty, recentlySuccessful } = useForm({ visibility });
    const [search, setSearch] = useState('');
    const groups = [...new Set(Object.values(modules).map(module => module.group))];
    const enabled = Object.values(data.visibility).filter(Boolean).length;
    const setAll = (value: boolean) => setData('visibility', Object.fromEntries(Object.keys(modules).map(key => [key, value])));

    return <PageTemplate title="Visibilité des modules" url={route('settings.modules.index')}
        description="Personnalisez les menus et les tableaux de bord des entreprises et de leurs utilisateurs.">
        <form onSubmit={event => { event.preventDefault(); put(route('settings.modules.update'), { preserveScroll: true }); }} className="space-y-6">
            <p className="text-sm text-muted-foreground">
                Ces réglages s’appliquent à toutes les entreprises de cette installation. Un module masqué disparaît des menus et des éléments correspondants du tableau de bord. Ses données et ses droits d’accès sont conservés ; ses pages restent accessibles par leur adresse aux utilisateurs autorisés.
            </p>
            <div className="flex flex-wrap items-center gap-3">
                <Input aria-label="Rechercher un module" placeholder="Rechercher un module…" value={search} onChange={event => setSearch(event.target.value)} className="max-w-sm" />
                <Button type="button" variant="outline" onClick={() => setAll(true)}>Tout afficher</Button>
                <Button type="button" variant="outline" onClick={() => setAll(false)}>Tout masquer</Button>
                <span className="text-sm text-muted-foreground">{enabled} / {Object.keys(modules).length} modules affichés</span>
            </div>
            <p className="text-xs text-muted-foreground">L’accueil et les paramètres restent disponibles pour permettre la navigation et la réactivation des modules.</p>
            {groups.map(group => {
                const entries = Object.entries(modules).filter(([, module]) => module.group === group && t(module.label).toLocaleLowerCase().includes(search.toLocaleLowerCase()));
                if (!entries.length) return null;
                return <fieldset key={group} className="rounded-lg border p-4">
                    <legend className="px-2 font-semibold">{t(group)}</legend>
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        {entries.map(([key, module]) => <div key={key} className="flex items-center justify-between gap-4 rounded-md border p-3">
                            <label htmlFor={`module-${key}`} className="cursor-pointer text-sm">{t(module.label)}</label>
                            <div className="flex items-center gap-2">
                                <span className="text-xs text-muted-foreground">{data.visibility[key] ? 'Affiché' : 'Masqué'}</span>
                                <Switch id={`module-${key}`} checked={data.visibility[key]} onCheckedChange={checked => setData('visibility', { ...data.visibility, [key]: checked })} />
                            </div>
                        </div>)}
                    </div>
                </fieldset>;
            })}
            {!Object.values(modules).some(module => t(module.label).toLocaleLowerCase().includes(search.toLocaleLowerCase())) && <p>Aucun module trouvé.</p>}
            {Object.values(errors).length > 0 && <p role="alert" className="text-sm text-destructive">{Object.values(errors).join(' ')}</p>}
            <div className="sticky bottom-0 flex items-center gap-4 border-t bg-background py-4">
                <Button type="submit" disabled={processing || !isDirty}>{processing ? 'Enregistrement…' : 'Enregistrer les modifications'}</Button>
                {isDirty && <span className="text-sm text-muted-foreground">Modifications non enregistrées</span>}
                {recentlySuccessful && !isDirty && <span role="status" className="text-sm text-green-600">Réglages enregistrés.</span>}
            </div>
        </form>
    </PageTemplate>;
}
