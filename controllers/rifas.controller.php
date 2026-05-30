<?php

class RifasController
{
    public const TABLE = 'raffles';

    private const TICKET_INSERT_CHUNK = 400;

    public static function obtenerRifas()
    {
        $search = !empty($_POST['search']) ? trim((string)$_POST['search']) : '';
        $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : '';

        $where = ['1=1'];
        $params = [];

        if ($search !== '' && $status !== '') {
            $where[] = 'title_raffle LIKE :s AND status_raffle = :st';
            $params[':s'] = '%' . $search . '%';
            $params[':st'] = (int)$status;
        } elseif ($search !== '') {
            $where[] = 'title_raffle LIKE :s';
            $params[':s'] = '%' . $search . '%';
        } elseif ($status !== '') {
            $where[] = 'status_raffle = :st';
            $params[':st'] = (int)$status;
        }

        $sql = 'SELECT * FROM raffles WHERE ' . implode(' AND ', $where) . ' ORDER BY id_raffle DESC';
        $rows = Db::fetchAll($sql, $params);

        return ['success' => true, 'data' => $rows];
    }

    public static function crearRifa($data)
    {
        set_time_limit(0);

        $datos = [
            'title_raffle' => trim($data['title_raffle']),
            'description_raffle' => trim($data['description_raffle']),
            'promotions_raffle' => trim($data['promotions_raffle'] ?? ''),
            'price_raffle' => $data['price_raffle'],
            'digits_raffle' => (int)$data['digits_raffle'],
            'date_raffle' => $data['date_raffle'],
            'status_raffle' => (int)$data['status_raffle'],
        ];

        $idRifa = Db::insert(self::TABLE, $datos);
        if ($idRifa <= 0) {
            return ['success' => false, 'message' => 'Error al crear la rifa.'];
        }

        $cifras = (int)$data['digits_raffle'];
        $totalBoletos = (int)pow(10, $cifras);

        $pdo = Db::pdo();
        $batch = [];
        for ($i = 0; $i < $totalBoletos; $i++) {
            $batch[] = [
                'number_ticket' => str_pad((string)$i, $cifras, '0', STR_PAD_LEFT),
                'status_ticket' => 0,
                'id_raffle_ticket' => $idRifa,
            ];
            if (count($batch) >= self::TICKET_INSERT_CHUNK) {
                self::insertTicketsBatch($pdo, $batch);
                $batch = [];
            }
        }
        if ($batch !== []) {
            self::insertTicketsBatch($pdo, $batch);
        }

        return ['success' => true, 'message' => "Rifa creada con $totalBoletos boletos."];
    }

    /** @param list<array{number_ticket:string,status_ticket:int,id_raffle_ticket:int}> $rows */
    private static function insertTicketsBatch(PDO $pdo, array $rows): void
    {
        if ($rows === []) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($rows), '(?,?,?)'));
        $sql = 'INSERT INTO tickets (number_ticket, status_ticket, id_raffle_ticket) VALUES ' . $placeholders;
        $st = $pdo->prepare($sql);
        $flat = [];
        foreach ($rows as $r) {
            $flat[] = $r['number_ticket'];
            $flat[] = $r['status_ticket'];
            $flat[] = $r['id_raffle_ticket'];
        }
        $st->execute($flat);
    }

    public static function actualizarRifa($data)
    {
        $id = (int)$data['id_raffle'];
        $allowed = [
            'title_raffle', 'description_raffle', 'promotions_raffle', 'price_raffle',
            'digits_raffle', 'date_raffle', 'status_raffle',
        ];
        $clean = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                $clean[$k] = $data[$k];
            }
        }
        if ($clean === []) {
            return ['success' => false];
        }
        $n = Db::update(self::TABLE, $clean, 'id_raffle = :id', [':id' => $id]);

        return $n > 0 ? ['success' => true, 'message' => 'Rifa actualizada'] : ['success' => false];
    }

    public static function eliminarRifa($data)
    {
        $id = (int)$data['id_raffle'];
        Db::delete('tickets', 'id_raffle_ticket = :id', [':id' => $id]);
        $n = Db::delete(self::TABLE, 'id_raffle = :id', [':id' => $id]);

        return $n > 0 ? ['success' => true, 'message' => 'Rifa eliminada'] : ['success' => false];
    }
}
