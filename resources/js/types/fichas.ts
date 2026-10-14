export type Ficha = {
    id: number;
    estudiante: string;
    representante: string;
    cedula_estudiante: string;
    cedula_representante: string;
    telefono: string | null;
    causa_salida: string | null;
    fecha: string;
    hora: string | null;
    curso_id: number;
    curso: string | null;
};

export type FichaFilters = {
    search: string;
    curso_id: number | null;
};

export type FichaDia = {
    fecha: string;
    total: number;
    fichas: {
        id: number;
        estudiante: string;
        curso: string | null;
        hora: string | null;
    }[];
};

export type FichaPagination = {
    data: Ficha[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    from: number | null;
    to: number | null;
    total: number;
    current_page: number;
    last_page: number;
    per_page: number;
};
