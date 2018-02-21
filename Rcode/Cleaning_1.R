#The original dataset is cleaned to have only US Gold medal winners and related info. 
#2018 winners are added manually.
winter = read_csv("/Users/yanjunchen/Desktop/Visual Data/winter.csv")
US_Gold = subset(winter, Country == "USA" & Medal == "Gold")
write.csv(US_Gold, "US_Gold.csv")