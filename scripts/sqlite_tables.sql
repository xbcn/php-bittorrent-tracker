CREATE TABLE peer (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    torrentId INTEGER NOT NULL default '0',
    peerId BLOB NOT NULL,
    ip TEXT NOT NULL,
    port INTEGER NOT NULL default '0',
    left INTEGER NOT NULL default '0',
    registered INTEGER NOT NULL,
    updated INTEGER NOT NULL,
    UNIQUE (torrentId,peerId)
)

CREATE TABLE torrent (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    infoHash BLOB UNIQUE,
    downloaded INTEGER NOT NULL default '0'
);
