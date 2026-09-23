import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { PageTemplate } from '@/components/page-template';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';

type Field = { label: string; group: string; type?: string; required?: boolean; locked?: boolean; hint?: string; default?: string | null; options?: Record<string, string> };
type Configuration = Record<string, { visible: boolean; default: string | number | null }>;

export default function EmployeeFields({ fields, configuration }: { fields: Record<string, Field>; configuration: Configuration }) {
    const { t } = useTranslation();
    const { data, setData, put, processing, errors, isDirty, recentlySuccessful } = useForm({ configuration });
    const toggle = (key: string, visible: boolean) => {
        const next = { ...data.configuration, [key]: { ...data.configuration[key], visible } };
        if (key === 'branch_id' && !visible) {
            next.department_id = { ...next.department_id, visible: false };
            next.designation_id = { ...next.designation_id, visible: false };
        }
        if (key === 'department_id' && !visible) next.designation_id = { ...next.designation_id, visible: false };
        if (key === 'department_id' && visible) next.branch_id = { ...next.branch_id, visible: true };
        if (key === 'designation_id' && visible) {
            next.branch_id = { ...next.branch_id, visible: true };
            next.department_id = { ...next.department_id, visible: true };
        }
        setData('configuration', next);
    };
    const errorFor = (key: string) => Object.entries(errors).filter(([path]) => path.startsWith(`configuration.${key}.`)).map(([, message]) => message).join(' ');
    return <PageTemplate title="Champs des employés" url={route('settings.employee-fields.index')}
        description="Choisissez les champs à afficher et les valeurs à utiliser lorsqu’ils sont masqués.">
        <form className="space-y-6" onSubmit={event => { event.preventDefault(); put(route('settings.employee-fields.update'), { preserveScroll: true }); }}>
            <div className="space-y-2 rounded-lg border bg-muted/30 p-4 text-sm">
                <p>Ces réglages s’appliquent aux formulaires employés de toutes les entreprises, y compris la conversion d’un candidat.</p>
                <p>À la création, un champ masqué reçoit la valeur définie ici. « Non renseigné » laisse le champ vide lorsque la base l’autorise. Le statut de l’employé exige une valeur.</p>
                <p>À la modification, les valeurs et les fichiers déjà enregistrés sont conservés. Les informations restent consultables dans les fiches et les rapports.</p>
                <p>Les affectations masquées (site, département, poste, horaire et règle de présence) restent vides à la création, afin de ne pas affecter un employé à une autre entreprise.</p>
            </div>
            <Button type="button" variant="outline" onClick={() => setData('configuration', Object.fromEntries(Object.entries(data.configuration).map(([key, value]) => [key, { ...value, visible: true }])))}>Tout afficher</Button>
            {[...new Set(Object.values(fields).map(field => field.group))].map(group => <fieldset key={group} className="rounded-lg border p-4">
                <legend className="px-2 font-semibold">{t(group)}</legend>
                <div className="space-y-4">
                    {Object.entries(fields).filter(([, field]) => field.group === group).map(([key, field]) => {
                        const value = data.configuration[key];
                        const error = errorFor(key);
                        return <div key={key} className="grid gap-3 rounded-md border p-3 md:grid-cols-2">
                            <div>
                                <div className="flex items-center justify-between gap-4">
                                    <label htmlFor={`visible-${key}`} className="font-medium">{t(field.label)}</label>
                                    <div className="flex items-center gap-2">
                                        <span className="text-xs">{value.visible ? 'Affiché' : 'Masqué'}</span>
                                        <Switch id={`visible-${key}`} checked={value.visible} disabled={field.locked} onCheckedChange={checked => toggle(key, checked)} />
                                    </div>
                                </div>
                                {field.locked && <p className="mt-2 text-xs text-muted-foreground">Nécessaire à l’identité ou à la connexion ; ce champ reste visible.</p>}
                                {field.hint && <p className="mt-2 text-xs text-muted-foreground">{field.hint}</p>}
                            </div>
                            {!field.locked && !value.visible && <div className="space-y-1">
                                {field.type === 'none' ? <p className="text-sm text-muted-foreground">{key === 'employee_id' ? 'Valeur générée automatiquement' : 'Sans valeur à la création ; conservation à la modification'}</p> : <>
                                    <label htmlFor={`default-${key}`} className="text-sm">Valeur par défaut à la création {field.required ? '(obligatoire)' : '(facultative)'}</label>
                                    {field.type === 'select' ? <select id={`default-${key}`} className="h-10 w-full rounded-md border bg-background px-3" value={value.default ?? ''}
                                        onChange={event => setData('configuration', { ...data.configuration, [key]: { ...value, default: event.target.value || null } })}>
                                        {!field.required && <option value="">Non renseigné</option>}
                                        {Object.entries(field.options ?? {}).map(([option, label]) => <option key={option} value={option}>{t(label)}</option>)}
                                    </select> : <Input id={`default-${key}`} type={field.type} value={value.default ?? ''} placeholder="Non renseigné" min={field.type === 'number' ? 0 : undefined} step={field.type === 'number' ? '0.01' : undefined}
                                        onChange={event => setData('configuration', { ...data.configuration, [key]: { ...value, default: event.target.value || null } })} />}
                                </>}
                            </div>}
                            {error && <p role="alert" className="text-sm text-destructive md:col-span-2">{error}</p>}
                        </div>;
                    })}
                </div>
            </fieldset>)}
            {Object.keys(errors).length > 0 && <p role="alert" className="text-sm text-destructive">Vérifiez les valeurs signalées dans le formulaire.</p>}
            <div className="sticky bottom-0 flex flex-wrap items-center gap-4 border-t bg-background py-4">
                <Button type="submit" disabled={processing || !isDirty}>{processing ? 'Enregistrement…' : 'Enregistrer les modifications'}</Button>
                {isDirty && <span className="text-sm text-muted-foreground">Modifications non enregistrées</span>}
                {recentlySuccessful && !isDirty && <span role="status" className="text-sm text-green-600">Réglages enregistrés.</span>}
            </div>
        </form>
    </PageTemplate>;
}
