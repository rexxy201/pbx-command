<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    json_response(Database::query('SELECT id, name, host, port, username, status, codecs, max_channels, notes, created_at, updated_at FROM sip_trunks ORDER BY name'));
}
if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['name']) || empty($b['host'])) error_response('name and host required');
    $newId = Database::insert(
        'INSERT INTO sip_trunks (name, host, port, username, password, status, codecs, max_channels, notes) VALUES (?,?,?,?,?,?,?,?,?)',
        [$b['name'],$b['host'],(int)($b['port']??5060),$b['username']??null,$b['password']??null,$b['status']??'active',$b['codecs']??null,(int)($b['maxChannels']??30),$b['notes']??null]
    );
    json_response(Database::row('SELECT id,name,host,port,username,status,codecs,max_channels,notes,created_at,updated_at FROM sip_trunks WHERE id=?',[$newId]),201);
}
if ($method === 'PUT' && $id) {
    $b = get_json_body();
    Database::execute(
        'UPDATE sip_trunks SET name=?,host=?,port=?,username=?,status=?,codecs=?,max_channels=?,notes=?,updated_at=NOW() WHERE id=?',
        [$b['name'],$b['host'],(int)($b['port']??5060),$b['username']??null,$b['status']??'active',$b['codecs']??null,(int)($b['maxChannels']??30),$b['notes']??null,$id]
    );
    json_response(Database::row('SELECT id,name,host,port,username,status,codecs,max_channels,notes,created_at,updated_at FROM sip_trunks WHERE id=?',[$id]));
}
if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM sip_trunks WHERE id=?', [$id]);
    json_response(['ok'=>true]);
}
error_response('Not found', 404);
