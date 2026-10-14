import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, GraduationCap, IdCard, Users } from 'lucide-react';
import { useState } from 'react';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as cursosIndex } from '@/routes/cursos';
import { dashboard } from '@/routes';
import { index as fichasIndex } from '@/routes/fichas';
import type { DashboardInvitation, FichaDia } from '@/types';

type Props = {
    pendingInvitations?: DashboardInvitation[];
    stats: {
        totalFichas: number;
        totalCursos: number;
        fichasDelMes: number;
    };
    fichasPorDia: FichaDia[];
};

type TeamProps = {
    currentTeam: { slug: string } | null;
};

function formatDay(isoDate: string): string {
    return new Date(`${isoDate}T00:00:00`).toLocaleDateString('es-ES', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

export default function Dashboard({
    pendingInvitations = [],
    stats,
    fichasPorDia,
}: Props) {
    const { currentTeam } = usePage<TeamProps>().props;
    const teamSlug = currentTeam?.slug ?? '';

    const [showInvitations, setShowInvitations] = useState(
        pendingInvitations.length > 0,
    );

    return (
        <>
            <Head title="Dashboard" />

            <PendingInvitationsModal
                invitations={pendingInvitations}
                open={pendingInvitations.length > 0 && showInvitations}
                onOpenChange={setShowInvitations}
            />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard
                        title="Fichas registradas"
                        value={stats.totalFichas}
                        hint={`${stats.fichasDelMes} este mes`}
                        icon={IdCard}
                        href={fichasIndex(teamSlug).url}
                    />

                    <StatCard
                        title="Cursos activos"
                        value={stats.totalCursos}
                        hint="En el catálogo"
                        icon={GraduationCap}
                        href={cursosIndex(teamSlug).url}
                    />

                    <StatCard
                        title="Estado del sistema"
                        value={stats.totalFichas > 0 ? 'Activo' : 'Vacío'}
                        hint="Registra tu primera ficha para comenzar"
                        icon={Users}
                    />
                </div>

                <div className="border-sidebar-border/70 dark:border-sidebar-border flex flex-1 flex-col gap-4 overflow-hidden rounded-xl border">
                    <div className="flex items-center justify-between gap-4 p-4">
                        <div>
                            <h2 className="text-base font-medium">
                                Fichas por día
                            </h2>
                            <p className="text-muted-foreground text-sm">
                                Matrículas registradas recientemente, agrupadas
                                por fecha
                            </p>
                        </div>

                        <Button variant="outline" asChild>
                            <Link href={fichasIndex(teamSlug).url}>
                                Ver todas <ArrowRight />
                            </Link>
                        </Button>
                    </div>

                    <div className="flex flex-col gap-3 px-4 pb-4">
                        {fichasPorDia.map((dia) => (
                            <section
                                key={dia.fecha}
                                className="border-sidebar-border/70 dark:border-sidebar-border overflow-hidden rounded-lg border"
                            >
                                <header className="bg-muted/40 flex items-center justify-between gap-2 border-b px-3 py-2">
                                    <span className="text-sm font-medium first-letter:uppercase">
                                        {formatDay(dia.fecha)}
                                    </span>

                                    <Badge variant="secondary">
                                        {dia.total}{' '}
                                        {dia.total === 1 ? 'ficha' : 'fichas'}
                                    </Badge>
                                </header>

                                <ul className="divide-border divide-y">
                                    {dia.fichas.map((ficha) => (
                                        <li
                                            key={ficha.id}
                                            className="flex items-center justify-between gap-3 px-3 py-2 text-sm"
                                        >
                                            <span className="truncate font-medium">
                                                {ficha.estudiante}
                                            </span>

                                            <span className="flex shrink-0 items-center gap-2">
                                                <Badge variant="secondary">
                                                    {ficha.curso ?? '—'}
                                                </Badge>

                                                {ficha.hora ? (
                                                    <span className="text-muted-foreground font-mono text-xs tabular-nums">
                                                        {ficha.hora}
                                                    </span>
                                                ) : null}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </section>
                        ))}

                        {fichasPorDia.length === 0 ? (
                            <p className="text-muted-foreground py-10 text-center text-sm">
                                Todavía no hay fichas registradas.
                            </p>
                        ) : null}
                    </div>
                </div>
            </div>
        </>
    );
}

function StatCard({
    title,
    value,
    hint,
    icon: Icon,
    href,
}: {
    title: string;
    value: number | string;
    hint: string;
    icon: typeof IdCard;
    href?: string;
}) {
    const content = (
        <div className="border-sidebar-border/70 dark:border-sidebar-border flex items-start justify-between gap-4 rounded-xl border p-4">
            <div className="space-y-1">
                <p className="text-muted-foreground text-sm">{title}</p>
                <p className="text-2xl font-semibold">{value}</p>
                <p className="text-muted-foreground text-xs">{hint}</p>
            </div>

            <div className="bg-primary/10 text-primary rounded-lg p-2">
                <Icon className="size-5" />
            </div>
        </div>
    );

    return href ? (
        <Link href={href} className="transition-opacity hover:opacity-80">
            {content}
        </Link>
    ) : (
        content
    );
}

Dashboard.layout = (props: TeamProps) => ({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
    ],
});
