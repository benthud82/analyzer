from pydfs_lineup_optimizer import get_optimizer, Site, Sport

optimizer = get_optimizer(Site.DRAFTKINGS_CAPTAIN_MODE, Sport.FOOTBALL)

optimizer.load_players_from_csv("C:/Users/19722/PycharmProjects/NFL/NFL_Projections/Week12/captainmode_HOUvDET.csv")

lineups = optimizer.optimize(n=5)

for lineup in optimizer.optimize(n=5):
    print(lineup)
    print(lineup.players)  # list of players
    print(lineup.fantasy_points_projection)
    print(lineup.salary_costs)