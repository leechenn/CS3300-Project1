#The original dataset is cleaned to have only US Gold medal winners and related info. 
#2018 winners are added manually.
winter = read_csv("/Users/yanjunchen/Desktop/Visual Data/winter.csv")
US_Gold = subset(winter, Country == "USA" & Medal == "Gold")
write.csv(US_Gold, "US_Gold.csv")

location = read_xlsx("/Users/yanjunchen/Desktop/Visual Data/location.xlsx")
#add locations by merging two datasets by "City"
newdata = merge(US_Gold, location[, c("City","Location")], by = "City")
