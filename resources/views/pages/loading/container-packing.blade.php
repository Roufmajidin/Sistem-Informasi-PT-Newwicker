import BP3D from 'binpackingjs';

const { Bin, Item, Packer } = BP3D;

window.simulatePacking = function (
    container,
    itemGroups
) {
    const packer = new Packer();

    const bin = new Bin(
        'Container',
        container.p,
        container.l,
        container.t,
        999999
    );

    packer.addBin(bin);

    itemGroups.forEach((group) => {
        for (let i = 0; i < group.qty; i++) {
            packer.addItem(
                new Item(
                    `${group.name}-${i + 1}`,
                    group.p,
                    group.l,
                    group.t,
                    1
                )
            );
        }
    });

    packer.pack();

    return {
        packed: bin.items,
        unpacked: packer.unfitItems
    };
};
