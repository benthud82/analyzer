SELECT 
    Position AS 'Position',
    Name_ID AS 'Name + ID',
    Name AS 'Name',
    ID AS 'ID',
    CASE
        WHEN All_Position = 'FLEX' THEN 'UTIL'
        ELSE All_Position
    END AS 'Roster Position',
    Salary AS 'Salary',
    Game AS 'Game Info',
    TeamAbbrev AS 'TeamAbbrev',
    IFNULL(CASE
        WHEN
            COALESCE((SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'Rotowire'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3)),
                    (SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'Sportsline'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3)),
                    (SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'FSP'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3))) IS NOT NULL
        THEN
            (COALESCE((SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'Rotowire'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3)),
                    0) + COALESCE((SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'Sportsline'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3)),
                    0) + COALESCE((SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'FSP'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3)),
                    0)) / (CASE
                WHEN
                    (SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'Rotowire'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3)) IS NULL
                THEN
                    0
                ELSE 1
            END + CASE
                WHEN
                    (SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'Sportsline'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3)) IS NULL
                THEN
                    0
                ELSE 1
            END + CASE
                WHEN
                    (SELECT 
                            mlb_proj_points
                        FROM
                            betanalyzer.dfs_mlb_projections
                        WHERE
                            mlb_proj_weeknum = CURDATE()
                                AND mlb_proj_site = 'FSP'
                                AND (mlb_proj_player = playername
                                OR mlb_proj_player = alt1
                                OR mlb_proj_player = alt2
                                OR mlb_proj_player = alt3)) IS NULL
                THEN
                    0
                ELSE 1
            END)
    END,0) AS 'AvgPointsPerGame',
    (SELECT 
            mlb_proj_points
        FROM
            betanalyzer.dfs_mlb_projections
        WHERE
            mlb_proj_weeknum = CURDATE()
                AND mlb_proj_site = 'Rotowire'
                AND (mlb_proj_player = playername
                OR mlb_proj_player = alt1
                OR mlb_proj_player = alt2
                OR mlb_proj_player = alt3)) AS proj_roto,
    (SELECT 
            mlb_proj_points
        FROM
            betanalyzer.dfs_mlb_projections
        WHERE
            mlb_proj_weeknum = CURDATE()
                AND mlb_proj_site = 'Sportsline'
                AND (mlb_proj_player = playername
                OR mlb_proj_player = alt1
                OR mlb_proj_player = alt2
                OR mlb_proj_player = alt3)) AS proj_sportsline,
    (SELECT 
            mlb_proj_points
        FROM
            betanalyzer.dfs_mlb_projections
        WHERE
            mlb_proj_weeknum = CURDATE()
                AND mlb_proj_site = 'FSP'
                AND (mlb_proj_player = playername
                OR mlb_proj_player = alt1
                OR mlb_proj_player = alt2
                OR mlb_proj_player = alt3)) AS proj_fsp,
    AvgPointsPerGame AS dk_avg
FROM
    betanalyzer.dk_mlb_all
        LEFT JOIN
    betanalyzer.master_player_dk_MLB ON Name = playername
WHERE
    AvgPointsPerGame IS NOT NULL
