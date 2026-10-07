import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import FichaController from '@/actions/App/Http/Controllers/FichaController';
import AlertError from '@/components/alert-error';
import FichaFormFields from '@/components/ficha-form-fields';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { index as fichasIndex } from '@/routes/fichas';
import type { CursoOption } from '@/types';

type Props = {
    cursos: CursoOption[];
    defaults: { fecha: string; hora: string };
};

type TeamProps = {
    currentTeam: { slug: string } | null;
};

export default function FichasCreate({ cursos, defaults }: Props) {
    const { currentTeam } = usePage<TeamProps>().props;
    const teamSlug = currentTeam?.slug ?? '';

    return (
        <>
            <Head title="Nueva ficha" />

            <h1 className="sr-only">Nueva ficha</h1>

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        aria-label="Volver a las fichas"
                        asChild
                    >
                        <Link href={fichasIndex(teamSlug)}>
                            <ArrowLeft />
                        </Link>
                    </Button>

                    <Heading
                        variant="small"
                        title="Nueva ficha"
                        description="Registra la matrícula de un estudiante"
                    />
                </div>

                <Form
                    action={FichaController.store(teamSlug)}
                    options={{ preserveScroll: true }}
                    resetOnError
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <AlertError
                                errors={Object.values(errors).filter(
                                    (error): error is string =>
                                        typeof error === 'string',
                                )}
                                title="No se pudo registrar la ficha"
                            />

                            <FichaFormFields
                                cursos={cursos}
                                values={defaults}
                                errors={errors}
                                autoFocus
                            />

                            <div className="flex items-center gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Guardando...'
                                        : 'Guardar ficha'}
                                </Button>

                                <Button
                                    variant="secondary"
                                    asChild
                                    disabled={processing}
                                >
                                    <Link href={fichasIndex(teamSlug)}>
                                        Cancelar
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

FichasCreate.layout = (props: TeamProps) => ({
    breadcrumbs: [
        {
            title: 'Fichas',
            href: props.currentTeam ? fichasIndex(props.currentTeam.slug) : '/',
        },
        { title: 'Nueva ficha', href: '#' },
    ],
});
