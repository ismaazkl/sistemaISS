import { Link, router, usePage } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import { FileText, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import FichaController from '@/actions/App/Http/Controllers/FichaController';
import ConfirmDialog from '@/components/confirm-dialog';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    create as fichasCreate,
    edit as fichasEdit,
    index as fichasIndex,
    pdf as fichasPdf,
} from '@/routes/fichas';
import type { CursoOption, FichaFilters, FichaPagination } from '@/types';

type Props = {
    fichas: FichaPagination;
    cursos: CursoOption[];
    filters: FichaFilters;
};

type TeamProps = {
    currentTeam: { slug: string } | null;
};

export default function FichasIndex({ fichas, cursos, filters }: Props) {
    const { currentTeam } = usePage<TeamProps>().props;
    const teamSlug = currentTeam?.slug ?? '';

    const [search, setSearch] = useState(filters.search);
    const [cursoId, setCursoId] = useState(
        filters.curso_id ? String(filters.curso_id) : 'all',
    );

    const applyFilters = (search: string, cursoId: string) => {
        router.get(
            fichasIndex(teamSlug),
            {
                search: search || undefined,
                curso_id: cursoId === 'all' ? undefined : cursoId,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    useEffect(() => {
        const serverSearch = filters.search;
        const serverCursoId = filters.curso_id
            ? String(filters.curso_id)
            : 'all';

        if (search === serverSearch && cursoId === serverCursoId) {
            return;
        }

        const timeout = setTimeout(() => applyFilters(search, cursoId), 300);

        return () => clearTimeout(timeout);
    }, [search, cursoId, filters.search, filters.curso_id, teamSlug]);

    const hasFilters = filters.search !== '' || filters.curso_id !== null;

    return (
        <>
            <Head title="Fichas" />

            <h1 className="sr-only">Fichas</h1>

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <Heading
                        variant="small"
                        title="Fichas de matrícula"
                        description={`${fichas.total} ${fichas.total === 1 ? 'ficha registrada' : 'fichas registradas'}`}
                    />

                    <Button asChild>
                        <Link href={fichasCreate(teamSlug)}>
                            <Plus /> Nueva ficha
                        </Link>
                    </Button>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <form
                        onSubmit={(event) => {
                            event.preventDefault();
                            applyFilters(search, cursoId);
                        }}
                        className="relative flex-1"
                    >
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                        <Input
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Buscar por estudiante, representante o cédula..."
                            className="pl-9"
                            aria-label="Buscar fichas"
                        />
                    </form>

                    <Select
                        value={cursoId}
                        onValueChange={(value) => setCursoId(value)}
                    >
                        <SelectTrigger
                            className="w-full sm:w-72"
                            aria-label="Filtrar por curso"
                        >
                            <SelectValue placeholder="Todos los cursos" />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectItem value="all">
                                Todos los cursos
                            </SelectItem>

                            {cursos.map((curso) => (
                                <SelectItem
                                    key={curso.id}
                                    value={String(curso.id)}
                                >
                                    {curso.curso}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    {hasFilters ? (
                        <Button
                            variant="secondary"
                            onClick={() => {
                                setSearch('');
                                setCursoId('all');
                                applyFilters('', 'all');
                            }}
                        >
                            Limpiar
                        </Button>
                    ) : null}
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <Table className="text-xs">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="px-3">
                                    Estudiante
                                </TableHead>
                                <TableHead className="hidden px-3 lg:table-cell">
                                    Representante
                                </TableHead>
                                <TableHead className="px-3">Curso</TableHead>
                                <TableHead className="hidden px-3 sm:table-cell">
                                    Fecha y hora
                                </TableHead>
                                <TableHead className="bg-background sticky right-0 z-10 w-28 border-l px-3 text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {fichas.data.map((ficha) => (
                                <TableRow key={ficha.id} className="group">
                                    <TableCell className="px-3">
                                        <span className="font-medium">
                                            {ficha.estudiante}
                                        </span>
                                        <span className="text-muted-foreground block font-mono text-[11px]">
                                            {ficha.cedula_estudiante}
                                        </span>
                                    </TableCell>

                                    <TableCell className="hidden px-3 lg:table-cell">
                                        <span>{ficha.representante}</span>
                                        <span className="text-muted-foreground block font-mono text-[11px]">
                                            {ficha.cedula_representante}
                                        </span>
                                    </TableCell>

                                    <TableCell className="px-3">
                                        <Badge variant="secondary">
                                            {ficha.curso ?? '—'}
                                        </Badge>
                                    </TableCell>

                                    <TableCell className="text-muted-foreground hidden px-3 whitespace-nowrap sm:table-cell">
                                        {ficha.fecha}
                                        {ficha.hora ? ` ${ficha.hora}` : ''}
                                    </TableCell>

                                    <TableCell className="bg-background group-hover:bg-muted sticky right-0 z-10 border-l px-2 text-right">
                                        <div className="flex items-center justify-end gap-0.5">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8"
                                                aria-label={`Descargar reporte PDF de ${ficha.estudiante}`}
                                                asChild
                                            >
                                                <a
                                                    href={fichasPdf.url({
                                                        current_team: teamSlug,
                                                        ficha: ficha.id,
                                                    })}
                                                >
                                                    <FileText />
                                                </a>
                                            </Button>

                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8"
                                                aria-label={`Editar ficha de ${ficha.estudiante}`}
                                                asChild
                                            >
                                                <Link
                                                    href={fichasEdit({
                                                        current_team: teamSlug,
                                                        ficha: ficha.id,
                                                    })}
                                                >
                                                    <Pencil />
                                                </Link>
                                            </Button>

                                            <ConfirmDialog
                                                action={FichaController.destroy(
                                                    {
                                                        current_team: teamSlug,
                                                        ficha: ficha.id,
                                                    },
                                                )}
                                                title="Eliminar ficha"
                                                description={`¿Seguro que deseas eliminar la ficha de ${ficha.estudiante}? Esta acción no se puede deshacer.`}
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label={`Eliminar ficha de ${ficha.estudiante}`}
                                                    className="text-destructive hover:text-destructive size-8"
                                                >
                                                    <Trash2 />
                                                </Button>
                                            </ConfirmDialog>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}

                            {fichas.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-muted-foreground h-24 text-center"
                                    >
                                        No hay fichas para mostrar.
                                    </TableCell>
                                </TableRow>
                            ) : null}
                        </TableBody>
                    </Table>
                </div>

                {fichas.last_page > 1 ? (
                    <nav className="flex items-center justify-between gap-4">
                        <span className="text-muted-foreground text-sm">
                            Mostrando {fichas.from}–{fichas.to} de{' '}
                            {fichas.total}
                        </span>

                        <div className="flex items-center gap-1">
                            {fichas.links.map((link) =>
                                link.url === null ? (
                                    <span
                                        key="ellipsis"
                                        className="text-muted-foreground px-2 text-sm"
                                    >
                                        …
                                    </span>
                                ) : (
                                    <Link
                                        key={link.label}
                                        href={link.url}
                                        preserveScroll
                                        preserveState
                                        className={
                                            link.active
                                                ? 'bg-primary text-primary-foreground rounded-md px-3 py-1.5 text-sm'
                                                : 'hover:bg-accent rounded-md px-3 py-1.5 text-sm'
                                        }
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ),
                            )}
                        </div>
                    </nav>
                ) : null}
            </div>
        </>
    );
}

FichasIndex.layout = (props: TeamProps) => ({
    breadcrumbs: [
        {
            title: 'Fichas',
            href: props.currentTeam ? fichasIndex(props.currentTeam.slug) : '/',
        },
    ],
});
