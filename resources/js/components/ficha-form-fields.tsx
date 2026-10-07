import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { CursoOption } from '@/types';

type Props = {
    cursos: CursoOption[];
    values?: {
        estudiante?: string;
        representante?: string;
        cedula_estudiante?: string;
        cedula_representante?: string;
        telefono?: string | null;
        causa_salida?: string | null;
        fecha?: string;
        hora?: string | null;
        curso_id?: number | string;
    };
    errors?: Record<string, string | undefined>;
    /** Only the first field receives autofocus. */
    autoFocus?: boolean;
};

export default function FichaFormFields({
    cursos,
    values = {},
    errors = {},
    autoFocus,
}: Props) {
    return (
        <div className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="estudiante">Estudiante</Label>

                    <Input
                        id="estudiante"
                        name="estudiante"
                        defaultValue={values.estudiante}
                        placeholder="Nombre y apellido"
                        maxLength={120}
                        required
                        autoFocus={autoFocus}
                    />

                    <InputError message={errors.estudiante} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="representante">Representante</Label>

                    <Input
                        id="representante"
                        name="representante"
                        defaultValue={values.representante}
                        placeholder="Nombre y apellido"
                        maxLength={120}
                        required
                    />

                    <InputError message={errors.representante} />
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="cedula_estudiante">
                        Cédula del estudiante
                    </Label>

                    <Input
                        id="cedula_estudiante"
                        name="cedula_estudiante"
                        defaultValue={values.cedula_estudiante}
                        placeholder="10 dígitos"
                        inputMode="numeric"
                        pattern="[0-9]{10}"
                        maxLength={10}
                        required
                    />

                    <InputError message={errors.cedula_estudiante} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="cedula_representante">
                        Cédula del representante
                    </Label>

                    <Input
                        id="cedula_representante"
                        name="cedula_representante"
                        defaultValue={values.cedula_representante}
                        placeholder="10 dígitos"
                        inputMode="numeric"
                        pattern="[0-9]{10}"
                        maxLength={10}
                        required
                    />

                    <InputError message={errors.cedula_representante} />
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="telefono">Teléfono</Label>

                    <Input
                        id="telefono"
                        name="telefono"
                        defaultValue={values.telefono ?? ''}
                        placeholder="0999999999"
                        inputMode="tel"
                        pattern="[0-9]{7,15}"
                        maxLength={20}
                    />

                    <InputError message={errors.telefono} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="fecha">Fecha de matrícula</Label>

                    <Input
                        id="fecha"
                        name="fecha"
                        type="date"
                        defaultValue={values.fecha}
                        max={new Date().toISOString().slice(0, 10)}
                        required
                    />

                    <InputError message={errors.fecha} />
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="hora">Hora</Label>

                    <Input
                        id="hora"
                        name="hora"
                        type="time"
                        defaultValue={values.hora ?? ''}
                    />

                    <InputError message={errors.hora} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="causa_salida">Causa/Motivo de Salida</Label>

                    <Input
                        id="causa_salida"
                        name="causa_salida"
                        defaultValue={values.causa_salida ?? ''}
                        placeholder="Motivo de la salida"
                        maxLength={255}
                    />

                    <InputError message={errors.causa_salida} />
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="curso_id">Curso</Label>

                <Select
                    name="curso_id"
                    defaultValue={
                        values.curso_id ? String(values.curso_id) : undefined
                    }
                    required
                >
                    <SelectTrigger id="curso_id" className="w-full">
                        <SelectValue placeholder="Selecciona un curso" />
                    </SelectTrigger>

                    <SelectContent>
                        {cursos.map((curso) => (
                            <SelectItem key={curso.id} value={String(curso.id)}>
                                {curso.curso}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <InputError message={errors.curso_id} />
            </div>
        </div>
    );
}
