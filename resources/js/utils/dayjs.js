import dayjs from 'dayjs';
import 'dayjs/locale/es';

/**
 * dayjs ya configurado en español. Se importa desde aquí y no desde 'dayjs' a
 * secas para que el locale quede fijado una sola vez.
 *
 * Va aparte de app.js a proposito: asi dayjs viaja en el chunk perezoso del
 * cronograma en vez de engordar el bundle principal, que ya roza el limite de
 * 2 MiB del precache de la PWA.
 */
dayjs.locale('es');

export default dayjs;
