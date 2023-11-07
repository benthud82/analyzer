SELECT 
    *
FROM
    betanalyzer.dfs_mlb_projections
WHERE
    DATE(mlb_proj_weeknum) = CURDATE();
    
DELETE
FROM
    betanalyzer.dfs_nhl_projections
WHERE
    DATE(nhl_proj_weeknum) = CURDATE();