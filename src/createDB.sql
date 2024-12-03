CREATE TABLE IF NOT EXISTS users (
    userID INTEGER PRIMARY KEY AUTO_INCREMENT,
    userName VARCHAR(255) NOT NULL,
    userPassword VARCHAR(255) NOT NULL,
    balance INT
);

CREATE TABLE IF NOT EXISTS apiKeys (
    userID INTEGER NOT NULL,
    apiKey VARCHAR(255) NOT NULL,
    expirationDate DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS stats (
    userID INTEGER PRIMARY KEY,
    allTimeHigh INTEGER,
    longestWinStreak INTEGER,
    currentWinStreak INTEGER,
    longestLooseStreak INTEGER,
    currentLooseStreak INTEGER,
    lastLogin DATETIME,
    highestWin INTEGER,
    highestLoss INTEGER,
    playTime BIGINT,
    totalWins INTEGER,
    totalWinSum INTEGER,
    totalLosses INTEGER,
    totalLossSum INTEGER
);

CREATE TABLE IF NOT EXISTS history (
    userID INTEGER,
    timestamp DATETIME,
    gameID INTEGER,
    winLoss INTEGER
);

CREATE TABLE IF NOT EXISTS gameSpecificStats (
    userID INTEGER,
    gameID INTEGER,
    playTime BIGINT,
    wins INTEGER,
    winSum INTEGER,
    looses INTEGER,
    looseSum INTEGER
);

CREATE TABLE IF NOT EXISTS gameTimeSessions (
    gtID VARCHAR(255),
    userID INTEGER,
    gameID INTEGER,
    startTime DATETIME
);