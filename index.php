<!-- 2/22/2018
ChenLi -->
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Olympic Winter Games</title>
    <script src="https://d3js.org/d3.v4.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.9.0/highlight.min.js"></script>
    <script src="http://d3js.org/topojson.v2.min.js"></script>
    <style>
    div{
      text-align: center;
      margin:auto;
    }
    svg{
      border: 1px solid black;
      display:block;
      margin:auto;
    }
    .axisy text{
      font-size: 6px;
    }
    </style>

  </head>

  <body>
    <div>
    <p>Winter Olympic Games (Men VS Women)</p>
    </div>
    <svg id="svg1" height="800" width="1000"></svg>
    <div>
    <p>Winter Olympic Games For US (Men VS Women)</p>
    </div>
    <svg id="svg2" height="800" width="500"></svg>
    <div>
    <p>Winter Olympic Games For US (Medals--Time (black:total, pink:women, blue:men) )</p>
    </div>
    <!-- <svg id="svg3" height="500" width="800"></svg> -->
    <svg id="svg4" height="1000" width="1000"></svg>
    <script id="script1">
    var rawData, nestedData;
    var yearData;
    var countries;
    var timeScale, numberScale, genderScale,timeScaleForLine,numberScaleForLine;
    var hostPoints=[];
    var timeArray=[];
    var svg = d3.select("#svg1");
    var svg2 = d3.select("#svg2");
    var svg3 = d3.select("#svg3");
    var svg4 = d3.select("#svg4");
    var height = svg.attr("height");
    var width = svg.attr("width");
    var height2 = svg2.attr("height");
    var width2 = svg2.attr("width");
    var projection = d3.geoEquirectangular().center([0, 0]);
    var pathGenerator = d3.geoPath().projection(projection);
    var sectorScale = d3.scaleOrdinal(d3.schemeCategory20);
    parseTime = d3.timeParse("%Y");
    function parseLine(line){
      return {
        Year: line["Year"],
        Gender: line["Gender"],
        Sport: line["Sport"],
        City: line["City"],
        Location: JSON.parse(line["Location"])
      };
    };
    function parseLine2(line){
      return {
        Year: line["Year"],
        Gender: line["Gender"],
        Sport: line["Sport"],
        Medal: line["Medal"]
      };
    };
    d3.json("/datasets/world-50m.json", function (error, data) {
      console.log("worldmap");
      countries = topojson.feature(data, data.objects.countries);
      projection.fitExtent([[0,svg4.attr("height")/2], [svg4.attr("width"), svg4.attr("height")]], countries);

      // var interestingPoints = [[0,0], [-76, 42]];
       showMap();
    });

    d3.csv("/datasets/newdata.csv",parseLine,function(error,data){
       console.log("newdata load");
      rawData = data;
      nestedData = d3.nest()
      .key(function(d){return d.Year})
      .entries(data);
      nestedData.forEach(function(d,i){
        timeArray[i]=parseTime(d.key);
      });
      yearData = nestedData.map(function(year){
        var men=0;
        var women=0;
        var result = {Year:parseTime(year.key)};
        result.Number = year.values.length;
        year.values.forEach(function(d){
          if(d.Gender=="Men"){
            men++;
          }
          else{
            women++;
          }
        });
        result.Men = men;
        result.Women = women;
        result.City = year.values[0]["City"];
        result.Location = year.values[0]["Location"];
        return result;
      });
      yearData.sort(function(objA,objB){
        return Number(objA["Year"])-Number(objB["Year"]);
      });

      yearData.forEach(function(d){
        var array=[d["Location"][0],d["Location"][1],d["Number"],d["City"],d["Year"]];
        hostPoints.push(array);
      });

      genderScale = d3.scaleOrdinal()
      .domain(["Men","Women"])
      .range(["#5C9FEC","#EB7BA1"]);
      var timeExtent = d3.extent(yearData,function(d){return d.Year});
      timeScale = d3.scaleTime().domain(timeExtent).range([30,height-30]);
      var numberExtent = d3.extent(yearData,function(d){return d.Number});
      numberScale = d3.scaleLinear().domain([0,120]).range([width/2,width-30]);
      numberScale2 = d3.scaleLinear().domain([0,25]).range([width2/2,width2-30]);
      numberScaleR = d3.scaleLinear().domain([0,120]).range([width/2,30]);
      numberScaleR2 = d3.scaleLinear().domain([0,25]).range([width2/2,30]);
      numberScaleForLine = d3.scaleLinear().domain([0,numberExtent[1]]).range([400,50]);
      timeScaleForLine = d3.scaleTime().domain(timeExtent).range([40,960]);
      var timeAxisForLine = d3.axisBottom(timeScaleForLine);
      timeAxisForLine.tickValues(timeArray);
      showdata(yearData,svg,numberScale);
      showdata(yearData,svg2,numberScale2);
      var timeAxis = d3.axisLeft(timeScale);
      timeAxis.tickValues(timeArray).tickSize(0);
      svg.append("g")
      .attr("class","axisy")
      .attr("transform","translate(500,0)")
      .call(timeAxis);
      var numberAxis = d3.axisBottom(numberScale);
      var numberAxisR = d3.axisBottom(numberScaleR);
      var numberAxisForLine = d3.axisLeft(numberScaleForLine);
      svg.append("g")
      .call(numberAxis);
      svg.append("g")
      .call(numberAxisR);
      svg2.append("g")
      .attr("class","axisy")
      .attr("transform","translate(250,0)")
      .call(timeAxis);
      var numberAxis = d3.axisBottom(numberScale2);
      var numberAxisR2 = d3.axisBottom(numberScaleR2);
      svg2.append("g")
      .call(numberAxis);
      svg2.append("g")
      .call(numberAxisR2);
      numberAxisForLine.tickSize(2);
      svg4.append("g")
      .call(numberAxisForLine)
      .attr("transform","translate(20,0)");
      svg4.append("g")
      .call(timeAxisForLine)
      .attr("class","axisy")
      .attr("transform","translate(0,400)");
      showLine(yearData,"men");
      showLine(yearData,"women");
      showLine(yearData,"both");

    });

    d3.csv("/datasets/winter.csv",parseLine2,function(error,data){
      rawData = data;
      nestedData = d3.nest()
      .key(function(d){return d.Year})
      .key(function(d){return d.Medal})
      .entries(data);
      nestedData.forEach(function(d,i){
        timeArray[i]=parseTime(d.key);
      });
      yearData = nestedData.map(function(year){
        var men=0;
        var women=0;
        var result = {Year:parseTime(year.key)};
        var goldArray;
        year["values"].forEach(function(d){
          if(d.key=="Gold")
          goldArray=d;
        });
        result.Number = goldArray.values.length;

      goldArray["values"].forEach(function(d){
          if(d.Gender=="Men"){
            men++;
          }
          else{
            women++;
          }
        });
        result.Men = men;
        result.Women = women;
        return result;
      });
      genderScale = d3.scaleOrdinal()
      .domain(["Men","Women"])
      .range(["#5C9FEC","#EB7BA1"]);
      var timeExtent = d3.extent(yearData,function(d){return d.Year});
      timeScale = d3.scaleTime().domain(timeExtent).range([30,height-30]);
      var numberExtent = d3.extent(yearData,function(d){return d.Number});
      numberScale = d3.scaleLinear().domain([0,120]).range([width/2,width-30]);
      showdata(yearData,svg,numberScale);
      var timeAxis = d3.axisLeft(timeScale);

      timeAxis.tickValues(timeArray).tickSize(0);
      svg.append("g")
      .attr("class","axisy")
      .attr("transform","translate(500,0)")
      .call(timeAxis);
      var numberAxis = d3.axisBottom(numberScale);
      svg.append("g")
      .call(numberAxis);

    });
     function showdata(data,svg,numberScale){
       data.forEach(function(d,i){
         svg.append("line")
         .attr("x1",numberScale(0))
         .attr("x2",numberScale(d.Men))
         .attr("y1",timeScale(d.Year))
         .attr("y2",timeScale(d.Year))
         .style("stroke",genderScale("Men"))
         .style("stroke-width",15)
         .style("opacity",0.5);
         svg.append("line")
         .attr("x1",numberScale(0))
         .attr("x2",numberScale(-d.Women))
         .attr("y1",timeScale(d.Year))
         .attr("y2",timeScale(d.Year))
         .style("stroke",genderScale("Women"))
         .style("stroke-width",15)
         .style("opacity",0.5);
       });
     }
     function showLine(data,gender){
        console.log("showLine");
       var pathGenerator = d3.line()
       .x(function(d){return timeScaleForLine(d.Year); })
       .y(function(d){
         if(gender=="men"){
         return numberScaleForLine(d.Men);
       }
         if(gender=="women"){
           return numberScaleForLine(d.Women);
         }
         if(gender=="both"){
           return numberScaleForLine(d.Number);
         }
       });
       var pathData = pathGenerator(data);
       if(gender=="men"){
       svg4.append("path")
       .attr("d",pathData)
       .attr('stroke','blue')
       .attr('stroke-width','1px')
       .attr('fill','none');
     }
     if(gender=="women"){
     svg4.append("path")
     .attr("d",pathData)
     .attr('stroke','pink')
     .attr('stroke-width','1px')
     .attr('fill','none');
   }
   if(gender=="both"){
   svg4.append("path")
   .attr("d",pathData)
   .attr('stroke','black')
   .attr('stroke-width','1px')
   .attr('fill','none');
   svg4.selectAll(".bar-total")
    .data(data)
    .enter().append("rect")
      .attr("class", "bar")
      .attr("x", function(d) { return timeScaleForLine(d.Year)-5; })
      .attr("y", function(d) { return numberScaleForLine(d.Number); })
      .style("fill","black")
      .style("fill-opacity",0.5)
      .attr("width", 10)
      .attr("height", function(d) { return 400 - numberScaleForLine(d.Number)
      });
    svg4.selectAll(".bar-men")
     .data(data)
     .enter().append("rect")
       .attr("class", "bar-men")
       .attr("x", function(d) { return timeScaleForLine(d.Year)-15; })
       .attr("y", function(d) { return numberScaleForLine(d.Men); })
       .style("fill","blue")
       .style("fill-opacity",0.5)
       .attr("width", 10)
       .attr("height", function(d) { return 400 - numberScaleForLine(d.Men)
       });
     svg4.selectAll(".bar-women")
      .data(data)
      .enter().append("rect")
        .attr("class", ".bar-women")
        .attr("x", function(d) { return timeScaleForLine(d.Year)+5; })
        .attr("y", function(d) { return numberScaleForLine(d.Women); })
        .style("fill","pink")
        .style("fill-opacity",0.5)
        .attr("width", 10)
        .attr("height", function(d) { return 400 - numberScaleForLine(d.Women)
        });
      svg4.selectAll(".bar-text")
       .data(data)
       .enter().append("text")
         .attr("class", "bar-text")
         .attr("x", function(d) { return timeScaleForLine(d.Year)-20; })
         .attr("y", function(d) { return numberScaleForLine(d.Number)-30;})
         .attr("fill",function(d,i){return sectorScale(i)})
         .text(function(d){return d.Men+"/"+d.Number+"/"+d.Women});
      svg4.append("text")
      .attr("x",30)
      .attr("y",30)
      .attr("font-size",20)
      .text("Men/Total/Women")

 }
     }

     function showMap() {
       console.log("showMap");
       hostPoints.forEach(function(d,i){
         var x=timeScaleForLine(d[4]);
         var circlex=projection([d[1],d[0]])[0];
         var circley=projection([d[1],d[0]])[1];
         var path=[[x,420],[x,460],[circlex,circley]];
         var connectionGenerator = d3.line();
         var pathString = connectionGenerator(path);
         svg4.append("path")
         .attr("d",pathString)
         .attr("fill","none")
         .attr("stroke",sectorScale(i));
         var number;
         if(i%2==0){
           number=10;
         }
         else{
           number=-10;
         }
         svg4.append("text")
         .attr("x",x+3)
         .attr("y",440+number)
         .text(d[3])
         .attr("font-size",8)
         .attr("fill",sectorScale(i));
       });

        pathGenerator = d3.geoPath().projection(projection);
        var paths = svg4.selectAll("path.country")
        .data(countries.features);
        paths = paths.enter().append("path")
        .attr("class", "country").merge(paths);
        paths.attr("d", function (country) {
          return pathGenerator(country);
        })
        .style("stroke","darkblue")
        .style("fill","lightblue")
        .style("opacity","0.5");
        var circles = svg4.selectAll("circle")
        .data(hostPoints);
        circles.exit().remove();
        circles = circles.enter().append("circle")
        .merge(circles);
        circles
        .attr("cx", function (d) {
          return projection([d[1],d[0]])[0];
        })
        .attr("cy", function (d) {
          return projection([d[1],d[0]])[1];
        })
        .attr("r", function (d) {
          return 2;
        })
        .attr("fill", "red")
        .attr("opacity", "1");

      }

    </script>
  </body>
</html>
